import { Hono } from "hono";
import { mq } from "./mqtt";
import * as fs from "fs/promises";
import { AppEventHandler } from "./abstract/eventHandler";
import { sub2regex } from "./utils/sub2regex";
import wsApp from "./ws";
import { appEvents } from "./appEvents";
import backendApi from "./backendApi";
import { initCleanupExpiredTrackingEntries } from "./expiredCleanup";

// Get args, if --debug, then set debug mode in process env
const args = process.argv.slice(2);
const debug = args.includes("--debug");
if (debug) {
    process.env.DEBUG = "1";
}

const app = new Hono();

app.get("/", (c) => {
    return c.text("OK");
});

app.route("/api/b", backendApi);

app.route("/ws", wsApp.wsApp);


const topics: [RegExp, AppEventHandler][] = [];
const loadedFiles: Set<string> = new Set();

// Get all files in the mqttEvents folder, import and register them
fs.readdir("./src/mqttEvents").then(async (files) => {
    for (const file of files) {
        if (loadedFiles.has(file)) {
            continue;
        }

        if (file.endsWith(".ts") || file.endsWith(".js")) {
            const event = await import(`./mqttEvents/${file}`);
            await mq.subscribeAsync(event.default.topic, event.default.options);
            topics.push([sub2regex(event.default.topic), event.default]);
            console.log(`Loaded file ${file}`);
        }
    }
});

mq.on("message", (topic, message) => {
    for (const [t, e] of topics) {
        if (t.test(topic)) {
            try {
                e.onMessage(message, topic);
            } catch (e) {
                console.error(
                    `Failed to handle message: ${e} handling topic ${topic}`
                );
            }
        }
    }
});

initCleanupExpiredTrackingEntries();

export default {
    port: 3001,
    fetch: app.fetch,
    websocket: wsApp.websocket
};
