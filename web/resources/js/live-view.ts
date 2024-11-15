import {createAppWsConnection, getAuthData, getWsUrl, type MappedServerWSPacketEvents} from "./ws-utils";
import type {TrackingEntry} from "./gen/ws_pb";
import {writable} from "svelte/store";
import {attachWsLiveMetrics, liveMetrics} from "./global-ws-store";
import {sha256} from "./utils";
import type {TypedEventTarget} from "typescript-event-target";
import type ReconnectingWebSocket from "reconnecting-websocket";
import arrivalAudio from "@/../audio/arrival-notification.wav";

const audio = new Audio(arrivalAudio);
const AUDIO_DEBOUNCE_PERIOD_MS = 5000;
let audioDebounce = 0;

export const trackingData = writable<Omit<TrackingEntry, "$typeName">[]>([]);

export interface LiveDisplayConfig {
    label?: string;
    title?: string;
}

export const liveDisplayConfig = writable<LiveDisplayConfig>({});

function attachWsTrackingData(ws: ReconnectingWebSocket, et: TypedEventTarget<MappedServerWSPacketEvents>) {
    et.addEventListener("fullTrackingUpdate", (e) => {
        const trackingEntries = e.detail.trackingEntries;
        trackingData.set(trackingEntries);
        console.log('trackingData length after fullTrackingUpdate:', trackingEntries.length);
    });

    et.addEventListener("trackingEntry", (e) => {
        const entry = e.detail;

        // Handle vehicle arrival or student departure
        if (entry.isActive) {
            // Vehicle arrival: Add new entry if it doesn't already exist
            trackingData.update((currentData) => {
                const existingEntry = currentData.find(
                    (trackingEntry) => trackingEntry.arrivalDepartureTrackingId === entry.arrivalDepartureTrackingId
                );

                if (!existingEntry) {
                    currentData.push({
                        arrivalDepartureTrackingId: entry.arrivalDepartureTrackingId,
                        entryTimestampMs: entry.entryTimestampMs,
                        timeoutTimestampMs: entry.timeoutTimestampMs,
                        isActive: entry.isActive,
                        vehicle: entry.vehicle,
                        students: entry.students,
                    });

                    // This means new data has been added, so we should play the audio
                    if (Date.now() - audioDebounce > AUDIO_DEBOUNCE_PERIOD_MS) {
                        audio.play();
                    }

                    audioDebounce = Date.now();
                } else {
                    // Merge the data
                    if ("timeoutTimestampMs" in entry) {
                        existingEntry.timeoutTimestampMs = entry.timeoutTimestampMs;
                    }

                    if ("absolutePosition" in entry) {
                        existingEntry.absolutePosition = entry.absolutePosition;
                    }

                    if ("isPinned" in entry) {
                        existingEntry.isPinned = entry.isPinned;
                    }
                }
                return currentData;
            });
        } else {
            // Student departure: Update the isActive field to false
            trackingData.update((currentData) => {
                return currentData.map((trackingEntry) => {
                    if (trackingEntry.arrivalDepartureTrackingId === entry.arrivalDepartureTrackingId) {
                        return {
                            ...trackingEntry,
                            isActive: false,
                        };
                    }
                    return trackingEntry;
                });
            });
        }
    });
}

async function runLiveView(cleanupFunctions: Function[]) {
    console.log("Running live view");

    // Connect to the websocket
    const auth = await getAuthData("live-display");
    if (!auth) {
        liveMetrics.update(v => ({
            ...v,
            isAuthenticated: false,
            publicAuthIdentifier: "Error obtaining authentication key"
        }));

        return;
    }

    const [ws, et, wsCleanup] = createAppWsConnection(await getWsUrl(), auth as unknown as string[]);

    attachWsTrackingData(ws, et);

    // Attach live metrics
    cleanupFunctions.push(attachWsLiveMetrics(ws, et));

    // Listen to new configuration
    et.addEventListener("clientConfig", (e) => {
        if (e.detail.config.case !== "authenticated") return;

        const config = e.detail.config.value;
        console.log("Server sent new configuration: ", config);

        liveDisplayConfig.update((m) => ({
            ...m,
            label: config.clientLabel,
            title: config.title
        }));
    });

    cleanupFunctions.push(wsCleanup);

    sha256(auth[1]).then(hash => liveMetrics.update(v => ({...v, publicAuthIdentifier: hash})));
}

export function initializeLiveView() {
    const cleanupFunctions: Function[] = [];

    runLiveView(cleanupFunctions);

    // Return a callback to cleanup the websocket
    return () => {
        cleanupFunctions.forEach(cleanup => cleanup());
    }
}
