// Simulate as a reader. Accepting hex encoded from stdin
import { program } from "commander";
import { create, toBinary, toJson } from "@bufbuild/protobuf";
import * as mqtt from "mqtt";
import * as readline from "readline";
import {
    ReaderStatusMessageSchema,
    StudentDepartureMessageSchema,
    VehicleArrivalMessageSchema,
} from "../gen/main_pb";

const rl = readline.createInterface({
    input: process.stdin,
    output: process.stdout,
    terminal: false,
});

async function connect(
    username: string,
    password: string,
    mode: "vehicle" | "user"
) {
    const clientId = `reader_${mode}_${username}123`;

    const willData = create(ReaderStatusMessageSchema, {
        isOnline: false,
    });

    const client = await mqtt.connectAsync(
        process.env["MQTT_URL"] ?? "mqtt://localhost:1883",
        {
            username: username,
            password: password,
            protocolVersion: 5,
            clientId,
            will: {
                topic: `dev/readers/${username}/status`,
                payload: Buffer.from(
                    toBinary(ReaderStatusMessageSchema, willData)
                ),
                retain: true,
                qos: 2,
            },
        }
    );

    console.log(`Connected to mqtt server as ${clientId}`);

    const connectedStatusData = create(ReaderStatusMessageSchema, {
        clientId,
        isOnline: true,
        timestamp: BigInt(Date.now()),
    });

    client.publish(
        `dev/readers/${username}/status`,
        Buffer.from(
            toBinary(ReaderStatusMessageSchema, connectedStatusData)
        ),
        {
            qos: 2,
            retain: true
        }
    );

    client.on("reconnect", () => {
        console.log(`Reconnected to mqtt server as ${clientId}`);

        const connectedStatusData = create(ReaderStatusMessageSchema, {
            clientId,
            isOnline: true,
            timestamp: BigInt(Date.now()),
        });

        client.publish(
            `dev/readers/${username}/status`,
            Buffer.from(
                toBinary(ReaderStatusMessageSchema, connectedStatusData)
            ),
            {
                qos: 2,
            }
        );
    });

    client.on("error", (error) => {
        console.error("Error:", error);
    });

    return client;
}

async function read(): Promise<string> {
    return new Promise((resolve) => {
        rl.question("", (line) => {
            resolve(line);
        });
    });
}

program
    .command("vehicle")
    .description(
        "Simulate as a vehicle arrival reader. Accepting hex encoded from stdin"
    )
    .argument("username", "MQTT Username")
    .argument("password", "MQTT Password")
    .action(async (username, password) => {
        const client = await connect(username, password, "vehicle");

        while (true) {
            process.stdout.write("(hex tagId)>> ");
            const input = await read();

            try {
                const data = Buffer.from(input, "hex");
                const arrivalData = create(VehicleArrivalMessageSchema, {
                    timestamp: BigInt(Date.now()),
                    tagId: data,
                });
                client.publish(
                    `events/vehicle_arrival`,
                    Buffer.from(
                        toBinary(VehicleArrivalMessageSchema, arrivalData)
                    ),
                    {
                        qos: 2,
                    }
                );
            } catch (e) {
                console.error(e);
            }
        }
    });

program
    .command("student")
    .description(
        "Simulate as a student reader. Accepting hex encoded from stdin"
    )
    .argument("username", "MQTT Username")
    .argument("password", "MQTT Password")
    .action(async (username, password) => {
        const client = await connect(username, password, "vehicle");

        while (true) {
            process.stdout.write("(hex tagId)>> ");
            const input = await read();

            try {
                const data = Buffer.from(input, "hex");
                const departureData = create(StudentDepartureMessageSchema, {
                    timestamp: BigInt(Date.now()),
                    tagId: data,
                });
                client.publish(
                    `events/student_departure`,
                    Buffer.from(
                        toBinary(StudentDepartureMessageSchema, departureData)
                    ),
                    {
                        qos: 2,
                    }
                );
            } catch (e) {
                console.error(e);
            }
        }
    });

program.parse();
