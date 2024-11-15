import os
from abc import ABC, abstractmethod
from dotenv import load_dotenv
from uhfreader.reader import CTI809
from uhfreader.connection import SerialConnection
from uhfreader.utils import hex_print
import mqtt
import main_pb2
from utils import get_timestamp


load_dotenv()


class App(ABC):
    @abstractmethod
    def run():
        pass


class VehicleArrivalApp(App):
    def __init__(self):
        PORT = os.environ["SERIAL_PORT"]
        BAUDRATE = int(os.environ["SERIAL_BAUDRATE"])
        TIMEOUT = int(os.environ["SERIAL_TIMEOUT"])

        self.conn = SerialConnection(PORT, BAUDRATE, timeout=TIMEOUT)
        try:
            reader = CTI809(b"\x00", self.conn)
            self.reader = reader
            reader.onscan(self.onscan)

            mode_state = (
                reader.MS_PROTO_18000_6C
                | reader.MS_OUT_RS
                | reader.MS_BEEP_ON
                | reader.MS_SYRIS485_DISABLE
            )
            reader.set_work_mode(
                reader.MODE_SCAN,
                mode_state,
                word_num=b"\x0A"
            )
            print("Work mode set")
        except Exception as e:
            self.conn.close()
            raise e
        self.client = mqtt.Client.from_environ(mqtt.Client.MODE_VEHICLE)
        self.client.on_connect = self.on_mqtt_connect
        self.client.on_connect_fail = self.on_mqtt_connect_fail
        self.client.on_message = self.on_mqtt_message

    def run(self):
        self.client.loop_start()
        while not self.client.is_connected():
            pass
        while True:
            try:
                self.reader.scan()
            except KeyboardInterrupt:
                break
            except Exception as e:
                self.conn.close()
                raise e
        self.conn.close()
        self.client.loop_stop()
        self.client.disconnect()

    def onscan(self, response):
        timestamp = get_timestamp()
        epc = self.reader.decompose_EPC(response["data"])["epc"]
        print()
        print("Tag scanned!")
        print("Timestamp:", timestamp)
        print("EPC (hex): ", end="")
        hex_print(epc)
        message = main_pb2.VehicleArrivalMessage(
            timestamp=timestamp,
            tagId=epc
        )
        self.client.publish_vehicle_arrival(message).wait_for_publish()

    def on_mqtt_connect(self, client, userdata, flags, reason_code, properties):
        print(f"MQTT connected with result code {reason_code}")
        self.client.publish_status()

    def on_mqtt_connect_fail(self, client, userdata):
        print("MQTT connection fail")

    def on_mqtt_message(self, client, userdata, msg):
        pass
