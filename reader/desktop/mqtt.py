import os
import paho.mqtt.client as mqtt
from dotenv import load_dotenv
import main_pb2
from utils import get_timestamp


load_dotenv()


class Client:
    MODE_VEHICLE = "vehicle"
    MODE_USER = "user"

    def __init__(self, host, port, keepalive, mode, username=None, password=None):
        assert mode == self.MODE_VEHICLE or mode == self.MODE_USER, "Invalid mode"

        self.client_id = username
        self._client = mqtt.Client(
            mqtt.CallbackAPIVersion.VERSION2,
            self.client_id,
            protocol=mqtt.MQTTv5,
            reconnect_on_failure=True
        )
        self._client.tls_set()

        # Authentication
        self._client.username_pw_set(username, password)

        # Will
        topic = f"dev/readers/{username}/status"
        self.reader_status_message = main_pb2.ReaderStatusMessage(
            isOnline=False,
            clientId=self.client_id
        )
        payload = self.reader_status_message.SerializeToString()
        self._client.will_set(topic=topic, payload=payload, qos=2, retain=True)

        self._client.connect(host, port, keepalive, clean_start=True)

    @property
    def on_connect(self):
        return self._client.on_connect

    @on_connect.setter
    def on_connect(self, value):
        self._client.on_connect = value

    @property
    def on_connect_fail(self):
        return self._client.on_connect_fail

    @on_connect_fail.setter
    def on_connect_fail(self, value):
        self._client.on_connect_fail = value

    @property
    def on_message(self):
        return self._client.on_message

    @on_message.setter
    def on_message(self, value):
        self._client.on_message = value

    def publish_student_departure(self, message: main_pb2.StudentDepartureMessage):
        topic = "events/student_departure"
        return self._client.publish(topic, message.SerializeToString())

    def publish_vehicle_arrival(self, message: main_pb2.VehicleArrivalMessage):
        topic = "events/vehicle_arrival"
        return self._client.publish(topic, message.SerializeToString())

    def publish_status(self):
        message = self.reader_status_message
        message.isOnline = True
        message.timestamp = get_timestamp()
        topic = f"dev/readers/{self.client_id}/status"
        return self._client.publish(topic, message.SerializeToString())

    def loop_start(self):
        return self._client.loop_start()

    def loop_stop(self):
        return self._client.loop_stop()

    def loop_forever(self):
        return self._client.loop_forever()

    def disconnect(self):
        return self._client.disconnect()

    def is_connected(self):
        return self._client.is_connected()

    @staticmethod
    def from_environ(mode):
        client = Client(
            os.environ["MQTT_HOST"],
            int(os.environ["MQTT_PORT"]),
            int(os.environ["MQTT_KEEPALIVE"]),
            mode,
            os.environ["MQTT_USERNAME"],
            os.environ["MQTT_PASSWORD"]
        )
        return client
