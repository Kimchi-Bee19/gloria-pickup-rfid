/**
  * Gloria RFID - NFC Reader MCU
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
#include <Adafruit_PN532.h>
#include <ESP32MQTTClient.h>
#include <ESPDateTime.h>
#include "pb_encode.h"
#include "pb_decode.h"
#include "main.pb.h"
#include "env.h"

// PN532 SPI Pins
#define PN532_SCK (18)
#define PN532_MOSI (23)
#define PN532_SS (5)
#define PN532_MISO (19)

// Indicators
#define BUZZER_PIN (27)
#define YELLOW_LED_PIN (25)
#define GREEN_LED_PIN (33)

// Connection to PN532
Adafruit_PN532 nfc(PN532_SCK, PN532_MISO, PN532_MOSI, PN532_SS);

// WiFiFlientSecure for SSL/TLS support
WiFiClientSecure client;

// MQTT Client
ESP32MQTTClient mqtt;

// Reader status message
app_v1_ReaderStatusMessage readerStatusMessage = app_v1_ReaderStatusMessage_init_zero;

template<typename T>
struct Array {
  T *data;
  size_t length;

  Array() {
    this->data = nullptr;
    this->length = 0;
  }

  Array(T *data, size_t length) {
    this->data = data;
    this->length = length;
  }

  bool equals(T *data, size_t length) {
    if (length != this->length)
      return false;

    for (int i = 0; i < length; i++)
      if (this->data[i] != data[i])
        return false;

    return true;
  }
};

// Buffer the last 7 tag IDs for debounce
Array<uint8_t> lastTagId(new uint8_t[7], 7);
time_t lastTimestamp = 0;
const time_t maxDebounceMs = 3000;

// Write bytes for protobuf
bool write_bytes(pb_ostream_t *stream, const pb_field_t *field, void *const *arg) {
  Array<uint8_t> *arr = (Array<uint8_t> *)*arg;
  if (!pb_encode_tag_for_field(stream, field))
    return false;

  return pb_encode_string(stream, arr->data, arr->length);
}

bool publish_student_departure(uint8_t *uid, size_t uidLength) {
  uint8_t buffer[128];
  app_v1_StudentDepartureMessage message = app_v1_StudentDepartureMessage_init_zero;
  pb_ostream_t stream = pb_ostream_from_buffer(buffer, sizeof(buffer));

  time_t timestamp = DateTime.now() * 1000;
  Serial.print("Timestamp: ");
  message.timestamp = timestamp;
  Array<uint8_t> arr(uid, uidLength);
  message.tagId.arg = &arr;
  message.tagId.funcs.encode = &write_bytes;
  bool status = pb_encode(&stream, app_v1_VehicleArrivalMessage_fields, &message);
  uint16_t message_length = stream.bytes_written;
  Serial.print("Stream length: ");
  Serial.println(message_length);
  Serial.print("Stream content: ");
  nfc.PrintHex(buffer, message_length);
  Serial.println("");

  if (!status)
    return false;

  status = mqtt.publish(String("events/student_departure"), (char *)buffer, message_length, 2, false);

  if (!status)
    return false;

  return true;
}

void beep(const int beepMs) {
  digitalWrite(BUZZER_PIN, HIGH);
  delay(beepMs);
  digitalWrite(BUZZER_PIN, LOW);
}

void setup(void) {
  pinMode(BUZZER_PIN, OUTPUT);
  pinMode(YELLOW_LED_PIN, OUTPUT);
  pinMode(GREEN_LED_PIN, OUTPUT);
  Serial.begin(115200);

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
    Serial.println("Failed to get time from server.");
    while (1)
      ;
  }

  mqtt.enableDebuggingMessages();
  mqtt.setURI(MQTT_URI, MQTT_USERNAME, MQTT_PASSWORD);
  mqtt.setMqttClientName(MQTT_USERNAME);

  // Set root CA
  client.setCACert(ROOT_CA);
  mqtt.setCaCert(ROOT_CA);

  nfc.begin();

  uint32_t versiondata = nfc.getFirmwareVersion();
  if (!versiondata) {
    Serial.print("Didn't find PN53x board");
    while (1)
      ;  // halt
  }
  // Got ok data, print it out!
  Serial.print("Found chip PN5");
  Serial.println((versiondata >> 24) & 0xFF, HEX);
  Serial.print("Firmware ver. ");
  Serial.print((versiondata >> 16) & 0xFF, DEC);
  Serial.print('.');
  Serial.println((versiondata >> 8) & 0xFF, DEC);

  Serial.println("Connecting to MQTT... ");

  mqtt.setKeepAlive(MQTT_KEEPALIVE);
  mqtt.enableLastWillMessage((String("dev/readers/") + MQTT_USERNAME + "/status").c_str(), "I am going offline");


  uint8_t buffer[128];
  pb_ostream_t stream = pb_ostream_from_buffer(buffer, sizeof(buffer));
  readerStatusMessage.isOnline = true;
  bool status = pb_encode(&stream, app_v1_ReaderStatusMessage_fields, &readerStatusMessage);

  mqtt.loopStart();
}


void loop(void) {
  digitalWrite(YELLOW_LED_PIN, HIGH);

  uint8_t success;
  uint8_t tagId[] = { 0, 0, 0, 0, 0, 0, 0 };  // Buffer to store the returned tag Id
  uint8_t tagIdLength;                        // Length of the tagId (4 or 7 bytes depending on ISO14443A card type)

  // Wait for an ISO14443A type cards (Mifare, etc.).  When one is found
  // 'tagId' will be populated with the tagId, and tagIdLength will indicate
  // if the tagId is 4 bytes (Mifare Classic) or 7 bytes (Mifare Ultralight)
  success = nfc.readPassiveTargetID(PN532_MIFARE_ISO14443A, tagId, &tagIdLength);

  if (success) {
    // Display some basic information about the card
    Serial.println("Found an ISO14443A card");
    Serial.print("  tagId Length: ");
    Serial.print(tagIdLength, DEC);
    Serial.println(" bytes");
    Serial.print("  tagId Value: ");
    nfc.PrintHex(tagId, tagIdLength);
    Serial.print("  tagId Hex: ");
    for(size_t i = 0; i < tagIdLength; i++){
      if(tagId[i] < 0x10) Serial.print(0);
      Serial.print(tagId[i], HEX);
    }
    Serial.println();

    time_t timestamp = DateTime.now() * 1000;
    if (lastTagId.equals(tagId, tagIdLength) && ((timestamp - lastTimestamp) < maxDebounceMs)) {
      // debounce
      Serial.println("Debouncing card, since it has been scanned recently.");
    } else {
      // Publish via MQTT
      publish_student_departure(tagId, tagIdLength);

      // Indicator
      digitalWrite(GREEN_LED_PIN, HIGH);
      beep(75);

      // Update last tagId and its timestamp
      memcpy(lastTagId.data, tagId, tagIdLength);
      lastTagId.length = tagIdLength;
      lastTimestamp = timestamp;
    }
    Serial.println("");
  }

  digitalWrite(GREEN_LED_PIN, LOW);
}

void onMqttConnect(esp_mqtt_client_handle_t client) {
  Serial.println("MQTT Connected!");
}

void handleMQTT(void *handler_args, esp_event_base_t base, int32_t event_id, void *event_data) {
  auto *event = static_cast<esp_mqtt_event_handle_t>(event_data);
  mqtt.onEventCallback(event);
}