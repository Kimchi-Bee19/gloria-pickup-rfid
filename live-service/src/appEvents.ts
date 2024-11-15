import EventEmitter from "node:events";
import { TrackingEntry } from "./gen/ws_pb";
import { ReaderStatusMessage } from "./gen/main_pb";

export interface AppEventMap {
    vehicleArrival: [trackingEntry: TrackingEntry];
    studentDeparture: [trackingEntry: TrackingEntry];
    trackingExpired: [trackingEntryAdmin: TrackingEntry];
    trackingModified: [trackingEntry: TrackingEntry];
    readerStatus: [username: string, readerStatus: ReaderStatusMessage];
}

export class AppEventEmitter extends EventEmitter<AppEventMap> {
    constructor() {
        super();
        this.setMaxListeners(0);
    }
    public register<K extends keyof AppEventMap>(
        eventName: K, listener: (...args: AppEventMap[K]) => void
    ) {
        super.on(eventName, listener as any);

        return () => {
            super.off(eventName, listener as any);
        };
    }
}
const appEvents = new AppEventEmitter();

export { appEvents };
