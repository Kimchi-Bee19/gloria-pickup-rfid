#pragma once

#include <vector>
#include <Arduino.h>
#include <mqtt_client.h>

void onMqttConnect(esp_mqtt_client_handle_t client);
/*
 * @brief Event handler registered to receive MQTT events
 *
 *  This function is called by the MQTT client event loop.
 *
 * @param handler_args user data registered to the event.
 * @param base Event base for the handler(always MQTT Base).
 * @param event_id The id for the received event.
 * @param event_data The data for the event, esp_mqtt_event_handle_t.
 */
void handleMQTT(void *handler_args, esp_event_base_t base, int32_t event_id, void *event_data);

typedef std::function<void(const String &message)> MessageReceivedCallback;
typedef std::function<void(const String &topicStr, const String &message)> MessageReceivedCallbackWithTopic;

class ESP32MQTTClient
{
private:
    esp_mqtt_client_config_t _mqtt_config; // C so different naming
    esp_mqtt_client_handle_t _mqtt_client;
	

    // MQTT related
    bool _mqttConnected;
    unsigned long _nextMqttConnectionAttemptMillis;
    unsigned int _mqttReconnectionAttemptDelay;
    const char *_mqttUri;
    const char *_mqttUsername;
    const char *_mqttPassword;
    const char *_mqttClientName;
    int _disableMQTTCleanSession;
    char *_mqttLastWillTopic;
    char *_mqttLastWillMessage;
    int _mqttLastWillQos;
    bool _mqttLastWillRetain;
    unsigned int _failedMQTTConnectionAttemptCount;

    int _mqttMaxInPacketSize;
    int _mqttMaxOutPacketSize;

    struct TopicSubscriptionRecord
    {
        String topic;
        MessageReceivedCallback callback;
        MessageReceivedCallbackWithTopic callbackWithTopic;
    };
    std::vector<TopicSubscriptionRecord> _topicSubscriptionList;

    // General behaviour related
    bool _enableSerialLogs;
    bool _drasticResetOnConnectionFailures;

public:
    ESP32MQTTClient(/* args */);
    ~ESP32MQTTClient();

    // Optional functionality
    void enableDebuggingMessages(const bool enabled = true);                                       // Allow to display useful debugging messages. Can be set to false to disable them during program execution
    void disablePersistence();                                                                 // Do not request a persistent connection. Connections are persistent by default. Must be called before the first loop() execution
    void enableLastWillMessage(const char *topic, const char *message, const bool retain = false); // Must be set before the first loop() call.
    void enableDrasticResetOnConnectionFailures() { _drasticResetOnConnectionFailures = true; }    // Can be usefull in special cases where the ESP board hang and need resetting (#59)

    void disableAutoReconnect();
    void setTaskPrio(int prio);

    /// Main loop, to call at each sketch loop()
    //void loop();

    // MQTT related
	void setClientCert(const char * clientCert);
	void setCaCert(const char * caCert);
	void setKey(const char * clientKey);
    void setOnMessageCallback();
    void setConnectionState(bool state);
    void setAutoReconnect(bool choice);
    bool setMaxOutPacketSize(const uint16_t size);
    bool setMaxPacketSize(const uint16_t size); // override the default value of 1024
    bool publish(const String &topic, const String &payload, int qos = 0, bool retain = false);
    bool publish(const String &topic, const char *payload, int len, int qos = 0, bool retain = false);
    bool subscribe(const String &topic, MessageReceivedCallback messageReceivedCallback, uint8_t qos = 0);
    bool subscribe(const String &topic, MessageReceivedCallbackWithTopic messageReceivedCallback, uint8_t qos = 0);
    bool unsubscribe(const String &topic);                                       // Unsubscribes from the topic, if it exists, and removes it from the CallbackList.
    void setKeepAlive(uint16_t keepAliveSeconds);                                // Change the keepalive interval (15 seconds by default)
    inline void setMqttClientName(const char *name) { _mqttClientName = name; }; // Allow to set client name manually (must be done in setup(), else it will not work.)
    inline void setURI(const char *uri, const char *username = "", const char *password = "")
    { // Allow setting the MQTT info manually (must be done in setup())
        _mqttUri = uri;
        _mqttUsername = username;
        _mqttPassword = password;
    };

    inline void setURL(const char *url, const uint16_t port, const char *username = "", const char *password = "")
    { // Allow setting the MQTT info manually (must be done in setup())
        char *uri=(char *)malloc(200);
        sprintf(uri,"mqtt://%s:%u", url, port);
        if (_enableSerialLogs)
            log_i("MQTT uri %s\n", uri);
        _mqttUri = uri;
        _mqttUsername = username;
        _mqttPassword = password;
    };

    inline bool isConnected() const { return _mqttConnected; };    
    inline bool isMyTurn(esp_mqtt_client_handle_t client) const { return _mqtt_client==client; }; // Return true if mqtt is connected

    inline const char *getClientName() { return _mqttClientName; };
    inline const char *getURI() { return _mqttUri; };

    void printError(esp_mqtt_error_codes_t *error_handle);

    // Default to onConnectionEstablished, you might want to override this for special cases like two MQTT connections in the same sketch
    
    bool loopStart();

    void onEventCallback(esp_mqtt_event_handle_t event);
    
private:
    void onMessageReceivedCallback(const char *topic, char *payload, unsigned int length);
    bool mqttTopicMatch(const String &topic1, const String &topic2);
};
