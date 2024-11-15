import { Hono } from "hono";
import { setupInitCache } from "./mqttEvents/setupInit";
import { readerStatusStore } from "./mqttEvents/readerStatus";
import { zValidator } from "@hono/zod-validator";
import z from "zod";
import { mq } from "./mqtt";
import { appEvents } from "./appEvents";
import { SetupConfigureMessageSchema, SetupInitMessage, StudentDepartureAdminMessageSchema } from "./gen/main_pb";
import { create, toBinary } from "@bufbuild/protobuf";
import { getConnInfo } from "hono/bun";
import { ipRestriction } from "hono/ip-restriction";
import {
    UnauthenticatedClientData,
    connectedClients,
    unauthenticatedClients,
} from "./ws";
import { DisplayLiveClient } from "./liveClient/DisplayLiveClient";
import { VehicleArrivalAdminMessageSchema } from "./gen/main_pb";


const app = new Hono();
// app.use(
//     "*",
//     ipRestriction(getConnInfo, {
//         denyList: [],
//         allowList: (process.env.BACKEND_IP_CIDR ?? "")
//             .split(",")
//             .map((v) => v.trim()),
//     })
// );

app.get("/identity-readers/poll-setup", async (c) => {
    // Return the ones in cache
    const cacheKeys = setupInitCache.keys();
    return c.json(
        cacheKeys
            .map((key) => setupInitCache.get(key) as SetupInitMessage)
            .map((v) => ({
                ...v,
                timestamp: Number(v.timestamp),
            }))
    );
});

app.get("/identity-readers/poll-status", async (c) => {
    // Return contents of readerStatusStore
    return c.json(
        Array.from(readerStatusStore.values()).map((v) => ({
            ...v,
            timestamp: Number(v.timestamp),
        }))
    );
});

const configureSchema = z.object({
    clientid: z.string().max(255),
    username: z.string().max(255),
    password: z.string().max(255),
});

app.post(
    "/identity-readers/configure",
    zValidator("json", configureSchema),
    async (c) => {
        const { clientid, username, password } = c.req.valid("json");

        // Send setup configure message
        await mq.publishAsync(
            `setup/${clientid}/configure`,
            Buffer.from(
                toBinary(
                    SetupConfigureMessageSchema,
                    create(SetupConfigureMessageSchema, {
                        username,
                        password,
                    })
                )
            ),
            {
                qos: 2,
            }
        );

        // Remove from cache
        unauthenticatedClients.set(clientid, undefined);

        // Wait for the reader to connect and send status message or timeout after 3 seconds
        let cancelCallbacks: (() => void)[] = [];
        const result = await Promise.race([
            new Promise<false>((resolve) => {
                let timeout = setTimeout(() => {
                    resolve(false);
                }, 5000);

                cancelCallbacks.push(() => {
                    clearTimeout(timeout);
                });
            }),
            new Promise<true>((resolve) => {
                cancelCallbacks.push(
                    appEvents.register("readerStatus", async (mUsername, m) => {
                        if (m.clientId === clientid && username === mUsername) {
                            resolve(true);
                        }
                    })
                );
            }),
        ]);

        cancelCallbacks.forEach((cb) => {
            try {
                cb();
            } catch (e) {}
        });

        if (result) {
            return c.json({
                result: "success",
            });
        } else {
            return c.json({
                result: "timeout",
            });
        }
    }
);

app.get("/ws/stats", async (c) => {
    return c.json({
        connectedClients: connectedClients.size,
    });
});

app.get("/ws/unauthenticated-clients", async (c) => {
    return c.json(
        unauthenticatedClients.keys().map((token) => {
            const d = unauthenticatedClients.get(
                token
            ) as UnauthenticatedClientData;

            if (!d) return null;
            return {
                token,
                humanReadableIdentifier: d.humanReadableIdentifier,
                fingerprintHex: Buffer.from(d.fingerprint).toString("hex"),
            };
        }).filter(Boolean)
    );
});

app.post("/live-display/on-update/:id", async (c) => {
    // Send config updates to connected clients with the same id.
    const receivedId = c.req.param("id");

    if (process.env.DEBUG) {
        console.log(`Received model update event for live display ${receivedId}`);
    }

    const clients = [...connectedClients.values()]
        .filter((c): c is DisplayLiveClient => c instanceof DisplayLiveClient)
        .filter((c) => c.liveDisplayId === receivedId);

    for (const client of clients) {
        await client.onModelUpdate();
    }
});

app.post("/live-display/new-entry/:id", async (c) => {
    const receivedId = c.req.param("id");

    if (process.env.DEBUG) {
        console.log(`Received new entry ID: ${receivedId}`);
    }

    // Create the message object
    const message = create(VehicleArrivalAdminMessageSchema, {
        timestamp: BigInt(Date.now()), // Convert timestamp to bigint
        id: BigInt(receivedId), // Convert ID to bigint
    });

    // Publish the message to the MQTT topic
    try {
        const binaryMessage = toBinary(VehicleArrivalAdminMessageSchema, message); // This returns a Uint8Array
        const bufferMessage = Buffer.from(binaryMessage); // Convert Uint8Array to Buffer
        await mq.publish("events/vehicle_arrival_admin", bufferMessage); // Now publish the Buffer
    } catch (error) {
        console.error("Failed to publish to MQTT:", error);
        return c.json({ success: false, error: "Failed to publish message" }, 500);
    }

    return c.json({ success: true, receivedId });
});

app.post("/live-display/mark-departed/:id", async (c) => {
    const receivedId = c.req.param("id");

    if (process.env.DEBUG) {
        console.log(`Received new entry ID: ${receivedId}`);
    }

    // Create the message object
    const message = create(StudentDepartureAdminMessageSchema, {
        timestamp: BigInt(Date.now()), // Convert timestamp to bigint
        id: BigInt(receivedId), // Convert ID to bigint
    });

    // Publish the message to the MQTT topic
    try {
        const binaryMessage = toBinary(StudentDepartureAdminMessageSchema, message); // This returns a Uint8Array
        const bufferMessage = Buffer.from(binaryMessage); // Convert Uint8Array to Buffer
        await mq.publish("events/student_departure_admin", bufferMessage); // Now publish the Buffer
    } catch (error) {
        console.error("Failed to publish to MQTT:", error);
        return c.json({ success: false, error: "Failed to publish message" }, 500);
    }

    return c.json({ success: true, receivedId });
});

app.post("/live-display/change-order/:id", async (c) => {
    const receivedId = c.req.param("id");

    if (process.env.DEBUG) {
        console.log(`Received new entry ID: ${receivedId}`);
    }
    // WIP
    // // Create the message object
    // const message = create(StudentDepartureAdminMessageSchema, {
    //     timestamp: BigInt(Date.now()), // Convert timestamp to bigint
    //     id: BigInt(receivedId), // Convert ID to bigint
    // });

    // // Publish the message to the MQTT topic
    // try {
    //     const binaryMessage = toBinary(StudentDepartureAdminMessageSchema, message); // This returns a Uint8Array
    //     const bufferMessage = Buffer.from(binaryMessage); // Convert Uint8Array to Buffer
    //     await mq.publish("events/student_departure_admin", bufferMessage); // Now publish the Buffer
    // } catch (error) {
    //     console.error("Failed to publish to MQTT:", error);
    //     return c.json({ success: false, error: "Failed to publish message" }, 500);
    // }

    return c.json({ success: true, receivedId });
});

export default app;
