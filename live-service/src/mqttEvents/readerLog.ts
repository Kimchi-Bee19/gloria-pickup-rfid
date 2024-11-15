import { AppEventHandler } from "../abstract/eventHandler";

export default {
    topic: "dev/readers/+/log",
    options: { qos: 2 },
    onMessage(message: Buffer) {
        if(process.env.DEBUG) {
            console.debug(`[dev/readers/+/log] > ${message.toString()}`);
        }
    },
} satisfies AppEventHandler;
