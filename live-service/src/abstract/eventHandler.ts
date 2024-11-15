import { IClientSubscribeOptions } from "mqtt";

export interface AppEventHandler {
    topic: string;
    options: IClientSubscribeOptions;

    onMessage(message: Buffer, topic: string): void;
}