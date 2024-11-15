import { fromBinary } from "@bufbuild/protobuf";
import { AppEventHandler } from "../abstract/eventHandler";
import { ReaderStatusMessage, ReaderStatusMessageSchema } from "../gen/main_pb";
import { appEvents } from "../appEvents";

/*
TODO: Make this resistant to memory leaks by removing dead clients from the list.
*/

type ReaderUsername = string;
export const readerStatusStore = new Map<ReaderUsername, ReaderStatusMessage>();

export default {
    topic: "dev/readers/+/status",
    options: { qos: 2, nl: true, rh: 1 },
    onMessage(rawMessage: Buffer, topic: string) {
        // Parse message, then log it
        const m = fromBinary(ReaderStatusMessageSchema, rawMessage);

        // Obtain the username from the topic
        const username = topic.substring(
            "dev/readers/".length,
            topic.lastIndexOf("/")
        );

        if (process.env.DEBUG) {
            console.debug(
                `[${topic}] > ts:${m.timestamp} online:${m.isOnline} clientId:${m.clientId}`
            );
        }

        if (!m.isOnline) readerStatusStore.delete(username);
        // Update the status in the store
        else readerStatusStore.set(username, m);

        // Broadcast as an app event
        appEvents.emit("readerStatus", username, m);
    },
} satisfies AppEventHandler;
