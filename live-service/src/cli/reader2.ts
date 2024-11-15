// Simulate as a reader. Accepting hex encoded from stdin
// How to use: bun run cli:reader2
import { program } from "commander";
import { create, fromBinary, toBinary, toJson } from "@bufbuild/protobuf";
import * as mqtt from "mqtt";
import * as readline from "readline";
import {
    ClientType,
    ReaderStatusMessage,
    ReaderStatusMessageSchema,
    SetupConfigureMessageSchema,
    SetupInitMessageSchema,
    StudentDepartureMessageSchema,
    VehicleArrivalMessageSchema,
} from "../gen/main_pb";
import z from "zod";
import { nanoid } from "nanoid";
import { resolve } from "bun";

// Settings setup
const envSchema = z.object({
    MQTT_URL: z.string().url().default("mqtt://localhost:1883"),
    CLI_SETTINGS_PATH: z.string().default("./reader-config.json"),
    MQTT_SETUP_KEY: z.string().max(255),
});

const readerConfigSchema = z.record(
    z.string(),
    z.object({
        username: z.string().max(255),
        password: z.string().max(255),
        clientid: z.string().max(255),
        as: z.enum(["vehicle", "student"]),
    })
);

// Validate the settings file
const env = envSchema.parse(process.env);
const config = readerConfigSchema.parse(
    (await Bun.file(env.CLI_SETTINGS_PATH).exists())
        ? await Bun.file(env.CLI_SETTINGS_PATH).json()
        : {}
);

// MQTT-related utils
async function setup(
    username: string,
    password: string,
    as: "vehicle" | "student"
): Promise<string> {
    // Generate a clientid
    const clientid = "readercli_" + nanoid(16);
    const configureTopic = `setup/${clientid}/configure`;

    const client = await mqtt.connectAsync(env["MQTT_URL"], {
        username: username,
        password: password,
        clientId: clientid,
        protocolVersion: 5,
    });

    console.log(`Connected to MQTT server as ${clientid}`);

    function publishInit() {
        const setupData = create(SetupInitMessageSchema, {
            timestamp: BigInt(Date.now()),
            clientId: clientid,
            firmwareVersion: "0.0.0",
            clientInfo: "Reader2 CLI",
            clientType:
                as === "student"
                    ? ClientType.STUDENT_READER
                    : ClientType.VEHICLE_READER,
        });

        client.publish(
            `setup/${clientid}/init`,
            Buffer.from(toBinary(SetupInitMessageSchema, setupData)),
            {
                qos: 2,
            }
        );
    }

    return new Promise((r) => {
        // Publish the setup message every 30 seconds
        const interval = setInterval(publishInit, 30000);

        // Subscribe to the configure topic
        client.subscribe(configureTopic);
        client.on("message", (topic, message) => {
            const m = fromBinary(SetupConfigureMessageSchema, message);
            console.log(`Received configure message from ${topic}`);

            config[m.username] = {
                username: m.username,
                password: m.password,
                clientid: clientid,
                as: as,
            };

            clearInterval(interval);
            client.end();
            r(m.username);
        });

        publishInit();

        console.log(
            `To proceed with setup, please add the reader in the admin UI.`
        );
    });
}

async function connect(username: string, password: string, clientid: string) {
    const willData = create(ReaderStatusMessageSchema, {
        isOnline: false,
    });

    const client = await mqtt.connectAsync(env["MQTT_URL"], {
        username: username,
        password: password,
        clientId: clientid,
        protocolVersion: 5,
        will: {
            topic: `dev/readers/${username}/status`,
            payload: Buffer.from(toBinary(ReaderStatusMessageSchema, willData)),
            retain: true,
            qos: 2,
        },
    });

    console.log(
        `Connected to MQTT server with username:${username} clientid:${clientid}`
    );

    const baseStatusData: Omit<ReaderStatusMessage, "$typeName"> = {
        isOnline: true,
        clientId: clientid,
        clientInfo: "Reader2 CLI",
        firmwareVersion: "0.0.0",
    };

    const connectedStatusData = create(ReaderStatusMessageSchema, {
        timestamp: BigInt(Date.now()),
        ...baseStatusData,
    });

    // Publish the current status message
    client.publish(
        `dev/readers/${username}/status`,
        Buffer.from(toBinary(ReaderStatusMessageSchema, connectedStatusData)),
        {
            qos: 2,
            retain: true,
        }
    );

    client.on("reconnect", () => {
        console.log(
            `Reconnected to MQTT server with username:${username} clientid:${clientid}`
        );

        const connectedStatusData = create(ReaderStatusMessageSchema, {
            timestamp: BigInt(Date.now()),
            ...baseStatusData,
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

let rl: readline.Interface;

async function read(question?: string): Promise<string> {
    if (!rl) {
        rl = readline.createInterface({
            input: process.stdin,
            output: process.stdout,
            terminal: false,
        });
    }

    return new Promise((resolve) => {
        rl.question(question ?? "", (line) => {
            resolve(line);
        });
    });
}

async function run(username: string) {
    const c = config[username];

    const client = await connect(c.username, c.password, c.clientid);

    while (true) {
        const input = await read("tag (hex) >> ");
        try {
            const data = Buffer.from(input, "hex");

            let binaryData: Buffer;

            if (c.as === "student") {
                const departureData = create(StudentDepartureMessageSchema, {
                    timestamp: BigInt(Date.now()),
                    tagId: data,
                });
                binaryData = Buffer.from(
                    toBinary(StudentDepartureMessageSchema, departureData)
                );
            } else {
                const arrivalData = create(VehicleArrivalMessageSchema, {
                    timestamp: BigInt(Date.now()),
                    tagId: data,
                });
                binaryData = Buffer.from(
                    toBinary(VehicleArrivalMessageSchema, arrivalData)
                );
            }

            await client.publishAsync(
                c.as === "student"
                    ? `events/student_departure`
                    : `events/vehicle_arrival`,
                binaryData,
                {
                    qos: 2,
                }
            );
        } catch (e) {
            console.error(e);
        }
    }
}

program
    .command("list")
    .alias("ls")
    .description("List registered readers")
    .action(() => {
        console.log("Registered readers:");
        for (const [username, c] of Object.entries(config)) {
            console.log(`${username}: ${c.as}`);
        }
        if (Object.keys(config).length === 0) {
            console.log("No readers registered.");
        }
    });

program
    .command("setup")
    .description("Setup a reader")
    .argument("<as>", "Reader type (student or vehicle)")
    .action(async (as) => {
        if (as !== "student" && as !== "vehicle") {
            console.error(
                "Invalid reader type. Must be either student or vehicle."
            );
            process.exit(1);
        }

        const username = await setup("setup", env.MQTT_SETUP_KEY, as);

        // Save the new config file and run
        Bun.write(env.CLI_SETTINGS_PATH, JSON.stringify(config, null, 4));
        await run(username);
    });

program
    .command("connect")
    .description("Connect as a reader")
    .argument("username", "MQTT Username")
    .action(async (username) => {
        // Check if the username is registered
        if (!config[username]) {
            console.error("Reader not registered.");
            process.exit(1);
        }

        await run(username);
    });

program.parse();
