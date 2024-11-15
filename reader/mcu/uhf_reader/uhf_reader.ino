/**
  * Gloria RFID - UHF Reader MCU
  * Tested and developed using an ESP32
  * 
  * Arduino IDE Settings:
  * Board Selection: DOIT ESP32 DEVKIT V1
*/

#include <time.h>
#include <WiFi.h>
#include <WiFiClientSecure.h>
#include <Wire.h>
#include <SPI.h>
#include <ESP32MQTTClient.h>
#include <ESPDateTime.h>
#include <UHFReader.h>
#include "pb_encode.h"
#include "pb_decode.h"
#include "main.pb.h"
#include "env.h"


class EPCDebouncer {
protected:
  size_t max_length, curr_length;
  EPCBlock *data;
  time_t *timestamp;
  time_t maxDebounceMs;
public:
  EPCDebouncer(size_t max_length, time_t maxDebounceMs=5000)
  {
    this->max_length = max_length;
    this->curr_length = 0;
    this->data = new EPCBlock[max_length];
    this->timestamp = new time_t[max_length];
    this->maxDebounceMs = maxDebounceMs;
  }

  // Try to insert new data
  // return false if debounced, otherwise true
  bool insert(EPCBlock &new_elem)
  {
    time_t new_timestamp = DateTime.now() * 1000;
    
    // Find exact match, and replace if last debounced was long ago
    for (int i = 0; i < this->curr_length; i++)
    {
      if (this->data[i].epcEqualsWith(new_elem))
      {
        if ((new_timestamp - this->timestamp[i]) > this->maxDebounceMs)
        {
          this->timestamp[i] = new_timestamp;
          return true;
        }

        // Negative edge case
        if ((new_timestamp - this->timestamp[i]) < 0)
        {
          this->timestamp[i] = new_timestamp;
          return true;
        }

        // Serial.print("  new_timestamp: ");
        // Serial.println(new_timestamp);

        // Serial.print("  this_timestamp: ");
        // Serial.println(this->timestamp[i]);

        // Serial.print("  diff: ");
        // Serial.println(new_timestamp - this->timestamp[i]);
        return false;
      }
    }

    // There are no exact match,
    // insert new if space available
    // or replace last recently used
    if (this->curr_length < this->max_length)
    {
      size_t index = this->curr_length++;
      this->data[index].set(new_elem);
      this->timestamp[index] = new_timestamp;
    }
    else
    {
      // Find index of last recently used
      size_t index = 0;
      for (int i = 1; i < this->curr_length; i++)
      {
        if (this->timestamp[i] < this->timestamp[index])
        {
          index = i;
        }
      }

      Serial.print("evicted index: ");
      Serial.println(index);

      this->data[index].set(new_elem);
      this->timestamp[index] = new_timestamp;
    }

    return true;
  }
} epc_debouncer(4);

SerialConnection conn = SerialConnection(&Serial2);
CTI809 reader(0x00, (Connection*)&conn);

// WiFiFlientSecure for SSL/TLS support
WiFiClientSecure client;

// MQTT Client
ESP32MQTTClient mqtt;

// Reader status message
app_v1_ReaderStatusMessage readerStatusMessage = app_v1_ReaderStatusMessage_init_zero;

bool write_bytes(pb_ostream_t *stream, const pb_field_t *field, void *const *arg) {
  Array<uint8_t> *arr = (Array<uint8_t>*)*arg;

  if (!pb_encode_tag_for_field(stream, field))
    return false;

  return pb_encode_string(stream, arr->data, arr->length);
}

bool publish_vehicle_arrival(uint8_t *tagId, size_t tagLength) {
  uint8_t buffer[2048];
  app_v1_VehicleArrivalMessage message = app_v1_VehicleArrivalMessage_init_zero;
  pb_ostream_t stream = pb_ostream_from_buffer(buffer, sizeof(buffer));

  time_t timestamp = DateTime.now() * 1000;  // in milliseconds
  Serial.print("  ts: ");
  Serial.println(timestamp);
  message.timestamp = timestamp;
  Array<uint8_t> arr(tagId, tagLength);
  message.tagId.arg = &arr;
  message.tagId.funcs.encode = &write_bytes;
  bool status = pb_encode(&stream, app_v1_VehicleArrivalMessage_fields, &message);
  size_t message_length = stream.bytes_written;

  if (!status)
  {
    Serial.println(stream.errmsg);
    return false;
  }

  status = mqtt.publish(String("events/vehicle_arrival"), (char *)buffer, message_length, 2, false);

  if (!status)
    return false;

  return true;
}

void setup(void) {
  pinMode(LED_BUILTIN, OUTPUT);
  digitalWrite(LED_BUILTIN, LOW);
  Serial.begin(115200);
  Serial.setTimeout(2000);
  Serial2.begin(9600, SERIAL_8N1, 16, 17);
  while (!Serial) delay(10);  // for Leonardo/Micro/Zero

  WiFi.begin(WIFI_SSID, WIFI_PASSWORD);
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }

  Serial.println("WiFi connected");
  Serial.println("IP address: ");
  Serial.println(WiFi.localIP());

  Serial.println("Synchronizing time");
  DateTime.setTimeZone("WIB-7");
  // this method config ntp and wait for time sync
  // default timeout is 10 seconds
  DateTime.begin();
  if (!DateTime.isTimeValid()) {
    Serial.println("Failed to get time from server. Halting...");
    while (1) {}
  }

  mqtt.enableDebuggingMessages();
  mqtt.setURI(MQTT_URI, MQTT_USERNAME, MQTT_PASSWORD);
  mqtt.setMqttClientName(MQTT_USERNAME);

  // Set root CA
  client.setCACert(ROOT_CA);
  mqtt.setCaCert(ROOT_CA);

  Serial.println("Setting work mode");

  uint8_t mode_state = (reader.MS_PROTO_18000_6C
                        | reader.MS_OUT_RS
                        | reader.MS_BEEP_OFF
                        | reader.MS_SYRIS485_DISABLE);
  int _status = reader.set_work_mode(reader.MODE_SCAN, mode_state, reader.MEM_EPC, 0x00, 0x0A);
  if (_status != 0)
  {
    Serial.print("Failed to set work mode. Status: ");
    Serial.println(_status);
    Serial.println("Halting...");
    while(1);
  }

  Serial.println("Connecting to MQTT... ");

  mqtt.setKeepAlive(MQTT_KEEPALIVE);
  mqtt.enableLastWillMessage((String("dev/readers/") + MQTT_USERNAME + "/status").c_str(), "I am going offline");


  uint8_t buffer[128];
  pb_ostream_t stream = pb_ostream_from_buffer(buffer, sizeof(buffer));
  readerStatusMessage.isOnline = true;
  bool status = pb_encode(&stream, app_v1_ReaderStatusMessage_fields, &readerStatusMessage);

  mqtt.loopStart();

  digitalWrite(LED_BUILTIN, HIGH);
}

void onscan(ResponseDataBlock *response) {
  EPCBlock epc;
  reader.decompose_EPC(response->data, response->data_length(), &epc);

  // Debounce
  if (!epc_debouncer.insert(epc))
  {
    // Serial.print("Debounce: ");
    // for(size_t i = 0; i < epc.epc_bytes_length; i++){
    //   if(epc.epc[i] < 0x10) Serial.print(0);
    //   Serial.print(epc.epc[i], HEX);
    // }
    // Serial.println();
    return;
  }

  Serial.println("New tag!");
  
  Serial.print("  tag epc hex: ");
  for(size_t i = 0; i < epc.epc_bytes_length; i++){
    if(epc.epc[i] < 0x10) Serial.print(0);
    Serial.print(epc.epc[i], HEX);
  }
  Serial.println();

  // Publish via MQTT
  if (publish_vehicle_arrival(epc.epc, epc.epc_bytes_length))
  {
    Serial.println("  tag published via MQTT!");
  }
  else
  {
    Serial.println("  failed to publish via MQTT");
  }
}

void loop(void) {
  uint8_t success;
  reader.onscan(onscan);
  success = reader.scan();
}

void onMqttConnect(esp_mqtt_client_handle_t client) {
  Serial.println("MQTT Connected!");
}

void handleMQTT(void *handler_args, esp_event_base_t base, int32_t event_id, void *event_data) {
  auto *event = static_cast<esp_mqtt_event_handle_t>(event_data);
  mqtt.onEventCallback(event);
}