import {createAppWsConnection, getAuthData, getWsUrl, type MappedServerWSPacketEvents} from "./ws-utils";
import type {TrackingEntry} from "./gen/ws_pb";
import {get, writable} from "svelte/store";
import {attachWsLiveMetrics, liveMetrics} from "./global-ws-store";
import {sha256} from "./utils";
import type {TypedEventTarget} from "typescript-event-target";
import type ReconnectingWebSocket from "reconnecting-websocket";

const AUDIO_DEBOUNCE_PERIOD_MS = 5000;
let audioDebounce = 0;
import arrivalAudios from './audio-import';

export const pronouncePlate = writable(false);
let audioQueue: HTMLAudioElement[] = [];
let isPlaying = false;

function enqueueAudio(audios: HTMLAudioElement[]) {
    audioQueue.push(...audios);
    if (!isPlaying) {
        playAudioQueue();
    }
}

function playAudioQueue() {

    if (!get(pronouncePlate)) {
        // If pronouncePlate is false, stop and clear the audio queue
        audioQueue = [];
        isPlaying = false;
        return;
    }

    if (audioQueue.length === 0) {
        isPlaying = false;
        return;
    }

    isPlaying = true;
    const currentAudio = audioQueue.shift();

    if (currentAudio) {
        currentAudio.play();

        currentAudio.addEventListener("ended", function onEnd() {
            currentAudio.removeEventListener("ended", onEnd);
            setTimeout(() => {
                playAudioQueue();
            }, 0); // Adjust delay as needed
        });
    }
}

pronouncePlate.subscribe(value => {
    if (!value && isPlaying) {
        // Stop current playback and clear the queue
        audioQueue = [];
        isPlaying = false;
    }
});

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
                    if (Date.now() - audioDebounce > AUDIO_DEBOUNCE_PERIOD_MS) {
                        let audioList: HTMLAudioElement[] = [];

                        entry.vehicle?.licensePlate.split("").forEach((char) => {
                            if (arrivalAudios[char]) {
                                audioList.push(arrivalAudios[char]);
                            }
                        });
                        audioList.push(arrivalAudios["Kelas"])
                        entry.students.forEach((student) => {
                            if (student.class.includes("SD")) {
                                audioList.push(arrivalAudios["SD"]);
                                student.class.substring(2).split("").forEach((char) => {
                                    if (arrivalAudios[char]) {
                                        audioList.push(arrivalAudios[char]);
                                    }
                                });
                            } else if (student.class.includes("SMP")) {
                                audioList.push(arrivalAudios["SMP"]);
                                student.class.substring(3).split("").forEach((char) => {
                                    if (arrivalAudios[char]) {
                                        audioList.push(arrivalAudios[char]);
                                    }
                                });
                            } else if (student.class.includes("TK")) {
                                audioList.push(arrivalAudios["TK"]);
                                student.class.substring(2).split("").forEach((char) => {
                                    if (arrivalAudios[char]) {
                                        audioList.push(arrivalAudios[char]);
                                    }
                                });
                            } else {
                                student.class.split("").forEach((char) => {
                                    if (arrivalAudios[char]) {
                                        audioList.push(arrivalAudios[char]);
                                    }
                                });
                            }
                        });
                        audioList.push(arrivalAudios["silent_half"])

                        enqueueAudio(audioList);
                    }
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
    const auth = await getAuthData("admin");
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
