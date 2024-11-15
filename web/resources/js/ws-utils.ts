import axios from "axios";
import ReconnectingWebSocket from "reconnecting-websocket";
import {nanoid} from "nanoid";
import {type ServerWSPacket, ServerWSPacketSchema} from "@/gen/ws_pb";
import {fromBinary} from "@bufbuild/protobuf";
import type {WebSocketEventListenerMap} from "reconnecting-websocket/dist/events";
import {TypedEventTarget} from "typescript-event-target";

export async function getAuthData(type: "admin" | "live-display") {
    if (type === "live-display") {
        if (!localStorage.getItem("token")) {
            localStorage.setItem("token", nanoid(32));
        }

        return ["token", localStorage.getItem("token") as string] as const;
    } else if (type === "admin") {
        const response = await axios.post(route("live-jwt"));
        if (response.status !== 200) {
            return false;
        }

        return ["jwt", response.data.token as string] as const;
    }

    return false;
}

export async function getWsUrl() {
    const response = await axios.get(route("live-ws-url"));
    return response.data;
}

function createWsConnection(wsPath: string, auth: string[]) {
    const ws = new ReconnectingWebSocket(wsPath, auth, {});
    ws.binaryType = "arraybuffer";

    return ws;
}

export function decodePacket(e: ArrayBuffer) {
    try {
        const data = fromBinary(ServerWSPacketSchema, new Uint8Array(e));
        return data.packet;
    } catch (err) {
        console.error("Failed to decode server message:", err);
    }

    return null;
}

export type MappedServerWSPacketEvents = {
    [K in Exclude<ServerWSPacket['packet']['case'], undefined>]: CustomEvent<Extract<ServerWSPacket['packet'], {
        case: K
    }>['value']>;
}

export function createAppWsConnection(wsPath: string, auth: string[]) {
    const ws = createWsConnection(wsPath, auth);
    const et = new TypedEventTarget<MappedServerWSPacketEvents>();

    // Extend the WebSocketEventMap with our own events
    ws.addEventListener("message", (e) => {
        const packet = decodePacket(e.data);

        if (packet) {
            if (packet.case === undefined) {
                console.error("Received invalid packet:", packet);
                return;
            }

            et.dispatchEvent(new CustomEvent(packet.case, {detail: packet.value}));
        } else {
            console.error("Received invalid packet:", e.data);
        }
    });

    const cleanup = () => {
        ws.close();
    }

    return [ws, et, cleanup] as const;
}
