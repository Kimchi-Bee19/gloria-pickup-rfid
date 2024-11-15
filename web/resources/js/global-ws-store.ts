import {writable} from "svelte/store";
import type {MappedServerWSPacketEvents} from "@/ws-utils";
import {ClientWSPacketSchema, PingPongSchema, PingPongType} from "@/gen/ws_pb";
import {create, toBinary} from "@bufbuild/protobuf";
import type ReconnectingWebSocket from "reconnecting-websocket";
import type {TypedEventTarget} from "typescript-event-target";

export interface LiveMetrics {
    rtt: number;
    time: Date;
    isConnected: boolean;
    isFirstConnection: boolean;
    isAuthenticated: boolean;
    authIdentifier?: string;
    publicAuthIdentifier: string;
}

export const liveMetrics = writable<LiveMetrics>({
    rtt: 0,
    time: new Date(),
    isConnected: false,
    isFirstConnection: true,
    isAuthenticated: false,
    publicAuthIdentifier: "",
});

let lastPingEvent = Date.now();
let serverClientTimeOffsetMs = 0;


export function attachWsLiveMetrics(ws: ReconnectingWebSocket, et: TypedEventTarget<MappedServerWSPacketEvents>) {
    ws.addEventListener("open", () => {
        console.log("Connected to websocket");
    });

    et.addEventListener("fullTrackingUpdate", () => {
        liveMetrics.update((m) => ({
            ...m,
            isConnected: true,
        }));
    });

    ws.addEventListener("close", (e) => {
        console.log(`Connection closed code: ${e.code}, reason: ${e.reason}`);

        liveMetrics.update((config) => {
            return {
                ...config,
                isFirstConnection: false,
                isAuthenticated: e.code !== 4001,
                authIdentifier: e.code === 4001 ? e.reason : undefined,
                isConnected: false,
            };
        });
    });

    ws.addEventListener("message", (e) => {
        // Receiving messages means it's authenticated
        liveMetrics.update(v => ({
            ...v,
            isAuthenticated: true
        }));
    });

    et.addEventListener("pingPong", (e) => {
        if (e.detail.type === PingPongType.PONG) {
            liveMetrics.update((m) => ({
                ...m,
                rtt: Date.now() - lastPingEvent,
                time: new Date(),
            }));

            lastPingEvent = Date.now();
        }
    });

    const intervalIds: number[] = [];

    // Time update on client
    intervalIds.push(setInterval(() => {
        // Update the time on the client side
        liveMetrics.update((m) => ({
            ...m,
            time: new Date(Date.now() + serverClientTimeOffsetMs),
        }));
    }, 1000));

    // Ping the server every 5 seconds if connected.
    intervalIds.push(setInterval(() => {
        if (Date.now() - lastPingEvent > 5000) {
            ws.send(toBinary(ClientWSPacketSchema, create(ClientWSPacketSchema, {
                packet: {
                    case: "pingPong",
                    value: create(PingPongSchema, {
                        $typeName: "app.v1.ws.PingPong",
                        type: PingPongType.PING,
                    }),
                },
            })));

            lastPingEvent = Date.now();
        }
    }, 5000));

    // Return a cleanup function
    return () => {
        intervalIds.forEach(clearInterval);
    };
}
