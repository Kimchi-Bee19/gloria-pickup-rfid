import { fromBinary } from "@bufbuild/protobuf";
import { AppEventHandler } from "../abstract/eventHandler";
import { SetupInitMessageSchema } from "../gen/main_pb";
import NodeCache from "node-cache";

export const setupInitCache = new NodeCache({ stdTTL: 60, checkperiod: 30 });

export default {
    topic: "setup/+/init",
    options: { qos: 2 },
    onMessage(message: Buffer) {
        const m = fromBinary(SetupInitMessageSchema, message);

        if(process.env.DEBUG) {
            console.debug(`[setup/+/init] >`, m);
        }

        setupInitCache.set(m.clientId, m);
    },
} satisfies AppEventHandler;
