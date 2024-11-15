// import {readable, writable} from "svelte/store";
// import {
//     ClientWSPacketSchema,
//     PingPongType,
//     ServerWSPacketSchema,
//     type TrackingEntry,
//     type TrackingEntryAdmin,
//     TrackingEntrySchema,
//     TrackingEntryAdminSchema,
// } from "@/gen/ws_pb";
// import ReconnectingWebSocket from "reconnecting-websocket";
// import {fromBinary, type Message, toBinary} from "@bufbuild/protobuf";
// import {nanoid} from "nanoid";
// import {sha256} from "@/utils";
// import axios from "axios";
//
// // Randomly generate a token for this display
// if (!localStorage.getItem("token")) {
//     localStorage.setItem("token", nanoid(32));
// }
//
// const token = localStorage.getItem("token") as string;
//
// axios.get("/api/live-ws-url").then(r => {
//     const wsPath = r.data ?? "ws://127.0.0.1:3001/ws";
//
//     const ws = new ReconnectingWebSocket(wsPath, ['token', token], {});
//     ws.binaryType = "arraybuffer";
//
//     ws.addEventListener("open", () => {
//         console.log("Connected to websocket");
//         configData.update((config) => {
//             return {
//                 ...config,
//                 isConnected: true,
//             };
//         });
//     });
//
//     ws.addEventListener("message", (e) => {
//         // Receiving messages means it's authenticated
//         configData.update(v => ({
//             ...v,
//             isAuthenticated: true
//         }));
//
//         try {
//             const data = fromBinary(ServerWSPacketSchema, new Uint8Array(e.data));
//             console.log(data.packet.case, data);
//
//             switch (data.packet.case) {
//                 case "pingPong": {
//                     configData.update((config) => {
//                         return {
//                             ...config,
//                             rtt: Date.now() - lastPingEvent,
//                         };
//                     });
//
//                     lastPingEvent = Date.now();
//                     break;
//                 }
//                 case "serverTime": {
//                     const serverTimeMs = data.packet.value.timestamp;
//                     // Recalculate the offset between the server and client time
//                     serverClientTimeOffsetMs = Number(serverTimeMs) - Date.now();
//
//                     break;
//                 }
//                 case "trackingEntry": {
//                     const entry = data.packet.value as TrackingEntry;
//
//                     // Handle vehicle arrival or student departure
//                     if (entry.isActive) {
//                         // Vehicle arrival: Add new entry if it doesn't already exist
//                         trackingData.update((currentData) => {
//                             const exists = currentData.some(
//                                 (trackingEntry) => trackingEntry.vehicle?.id === entry.vehicle?.id
//                             );
//
//                             if (!exists) {
//                                 currentData.push({
//                                     arrivalDepartureTrackingId: entry.arrivalDepartureTrackingId,
//                                     entryTimestampMs: entry.entryTimestampMs,
//                                     isActive: entry.isActive,
//                                     vehicle: entry.vehicle,
//                                     students: entry.students,
//                                 });
//                             }
//                             return currentData;
//                         });
//                     } else {
//                         // Student departure: Update the isActive field to false
//                         trackingData.update((currentData) => {
//                             return currentData.map((trackingEntry) => {
//                                 if (trackingEntry.arrivalDepartureTrackingId === entry.arrivalDepartureTrackingId) {
//                                     return {
//                                         ...trackingEntry,
//                                         isActive: false,
//                                     };
//                                 }
//                                 return trackingEntry;
//                             });
//                         });
//                     }
//
//                     break;
//                 }
//                 case "fullTrackingUpdate": {
//                     const trackingEntries = data.packet.value.trackingEntries;
//                     trackingData.set(trackingEntries);
//                     console.log('trackingData length after fullTrackingUpdate:', trackingEntries.length);
//                     break;
//                 }
//                 case "trackingEntryAdmin": {
//                     const entry = data.packet.value as TrackingEntryAdmin;
//
//                     // Handle vehicle arrival or student departure
//                     if (entry.isActive) {
//                         // Vehicle arrival: Add new entry if it doesn't already exist
//                         trackingDataAdmin.update((currentData) => {
//                             const exists = currentData.some(
//                                 (trackingEntryAdmin) => trackingEntryAdmin.vehicle?.id === entry.vehicle?.id
//                             );
//
//                             if (!exists) {
//                                 currentData.push({
//                                     arrivalDepartureTrackingId: entry.arrivalDepartureTrackingId,
//                                     entryTimestampMs: entry.entryTimestampMs,
//                                     isActive: entry.isActive,
//                                     vehicle: entry.vehicle,
//                                     students: entry.students,
//                                 });
//                             }
//                             return currentData;
//                         });
//                     } else {
//                         // Student departure: Update the isActive field to false
//                         trackingDataAdmin.update((currentData) => {
//                             return currentData.map((trackingEntryAdmin) => {
//                                 if (trackingEntryAdmin.arrivalDepartureTrackingId === entry.arrivalDepartureTrackingId) {
//                                     return {
//                                         ...trackingEntryAdmin,
//                                         isActive: false,
//                                     };
//                                 }
//                                 return trackingEntryAdmin;
//                             });
//                         });
//                     }
//
//                     break;
//                 }
//                 case "fullTrackingUpdateAdmin": {
//                     const trackingEntriesAdmin = data.packet.value.trackingEntriesAdmin;
//                     trackingDataAdmin.set(trackingEntriesAdmin);
//                     console.log('trackingDataAdmin length after fullTrackingUpdate:', trackingEntriesAdmin.length);
//                     break;
//                 }
//             }
//         } catch (e) {
//             console.error(e);
//         }
//     });
//
//     ws.addEventListener("close", (e) => {
//         console.log(`Connection closed code: ${e.code} reason: ${e.reason}`);
//         configData.update((config) => {
//             return {
//                 ...config,
//                 isAuthenticated: e.code !== 4001,
//                 authIdentifier: e.code === 4001 ? e.reason : undefined,
//                 isConnected: false,
//             };
//         });
//     });
//
//     setInterval(() => {
//         if (Date.now() - lastPingEvent > 5000) {
//             ws.send(toBinary(ClientWSPacketSchema, {
//                 $typeName: "app.v1.ws.ClientWSPacket",
//                 packet: {
//                     case: "pingPong",
//                     value: {
//                         $typeName: "app.v1.ws.PingPong",
//                         type: PingPongType.PING,
//                     }
//                 },
//             }));
//
//             lastPingEvent = Date.now();
//         }
//     }, 1000);
// });
//
// interface ScreenConfig {
//     rtt: number;
//     title: string;
//     time: Date;
//     isConnected: boolean;
//     isAuthenticated: boolean;
//     authIdentifier?: string;
//     publicAuthIdentifier: string;
// }
//
// // Temporary mock data
// export const trackingData = writable<Omit<TrackingEntry, "$typeName">[]>([]);
// export const trackingDataAdmin = writable<Omit<TrackingEntryAdmin, "$typeName">[]>([]);
// export const configData = writable<ScreenConfig>({
//     rtt: 0,
//     title: "Antrean Penjemputan",
//     time: new Date(),
//     isConnected: false,
//     isAuthenticated: false,
//     publicAuthIdentifier: "",
// });
//
// sha256(token).then(hash => configData.update(v => ({...v, publicAuthIdentifier: hash})));
//
// let lastPingEvent = Date.now();
// let serverClientTimeOffsetMs = 0;
//
//
// setInterval(() => {
//     // Update the time on the client side
//     configData.update((config) => {
//         return {
//             ...config,
//             time: new Date(Date.now() + serverClientTimeOffsetMs),
//         };
//     });
// }, 1000);
//
// configData.subscribe((config) => {
//     // console.log(config);
// });
