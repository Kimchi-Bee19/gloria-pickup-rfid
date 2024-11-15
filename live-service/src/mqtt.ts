import mqtt from "mqtt";

export const mq = mqtt.connect(process.env.MQTT_URL ?? "mqtt://localhost:1883", {
    username: process.env.MQTT_LIVE_SERVICE_USERNAME ?? "liveservice",
    password: process.env.MQTT_LIVE_SERVICE_PASSWORD ?? "a-very-secure-live-service-password",
    resubscribe: true
});

mq.on("connect", () => {
    console.log("MQTT client connected");
});

mq.on("reconnect", () => {
    console.log("MQTT client reconnecting...");
});

mq.on("error", (error) => {
    console.error("MQTT client error:", error);
});

// Before exit, disconnect
process.on("SIGINT", () => {
    mq.end();
});
