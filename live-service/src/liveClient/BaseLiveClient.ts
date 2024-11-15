import { create, fromBinary, toBinary } from "@bufbuild/protobuf";
import { $, ServerWebSocket } from "bun";
import { Context } from "hono";
import { getConnInfo } from "hono/bun";
import { ConnInfo } from "hono/conninfo";
import { WSContext, WSMessageReceive } from "hono/ws";
import { customAlphabet } from "nanoid";
import { AppEventMap, appEvents } from "../appEvents";
import { db } from "../db";
import {
    ClientWSPacket,
    ClientWSPacketSchema,
    FullTrackingUpdate,
    FullTrackingUpdateSchema,
    PingPongType,
    ServerWSPacket,
    ServerWSPacketSchema,
    StudentInformationSchema,
    TrackingEntry,
    TrackingEntrySchema,
} from "../gen/ws_pb";
import { TRACKING_TIMEOUT_SECONDS } from "../utils/constants";

export abstract class BaseLiveClient {
    protected ws: WSContext<ServerWebSocket> | undefined;
    protected readonly connInfo: ConnInfo;
    public connInfoStr: string;
    public lastFullRefreshScheduledMs: number;
    protected attachedEventCallbacks: (() => void)[];

    constructor(protected c: Context, protected cleanupFunction: () => void) {
        this.connInfo = getConnInfo(c);
        this.connInfoStr = `${this.connInfo.remote.address} - ${c.get(
            "clientName"
        )}`;
        this.attachedEventCallbacks = [];
        this.lastFullRefreshScheduledMs = 0;
    }

    protected filterTracking(trackingEntry: TrackingEntry) {
        return true;
    }

    protected abstract authenticate(): Promise<[boolean, string | null]>;

    protected async onOpen(e: Event, ws: WSContext<ServerWebSocket>) {
        this.ws = ws;
        const [authenticated, message] = await this.authenticate();

        if (!authenticated) {
            console.log(`Rejected authentication from ${this.connInfoStr}`);
            ws.close(4001, message ?? "Authentication failed");
            return;
        }

        console.log(`New connection from ${this.connInfoStr}`);

        // Listen to app events
        this.attachedEventCallbacks.push(
            appEvents.register(
                "vehicleArrival",
                this.onVehicleArrival.bind(this)
            )
        );

        this.attachedEventCallbacks.push(
            appEvents.register(
                "studentDeparture",
                this.onStudentDeparture.bind(this)
            )
        );

        this.attachedEventCallbacks.push(
            appEvents.register(
                "trackingExpired",
                this.onTrackingExpired.bind(this)
            )
        );

        this.attachedEventCallbacks.push(
            appEvents.register(
                "trackingModified",
                this.onTrackingModified.bind(this)
            )
        );
    }

    protected onVehicleArrival(e: AppEventMap["vehicleArrival"][0]) {
        console.log(`Sending vehicle arrival event to ${this.connInfoStr}`);

        // Filter tracking entry
        if (!this.filterTracking(e)) return;

        this.sendRaw(
            toBinary(
                ServerWSPacketSchema,
                create(ServerWSPacketSchema, {
                    packet: {
                        case: "trackingEntry",
                        value: create(TrackingEntrySchema, e),
                    },
                })
            )
        );
    }

    protected onTrackingModified(e: TrackingEntry) {
        // Filter tracking entry
        if (!this.filterTracking(e)) return;

        console.log(`Sending modified tracking to ${this.connInfoStr}`);
        this.sendRaw(
            toBinary(
                ServerWSPacketSchema,
                create(ServerWSPacketSchema, {
                    packet: {
                        case: "trackingEntry",
                        value: create(TrackingEntrySchema, e),
                    },
                })
            )
        );
    }

    protected onTrackingExpired(e: TrackingEntry) {
        // Filter tracking entry
        if (!this.filterTracking(e)) return;

        console.log(`Sending expired tracking to ${this.connInfoStr}`);
        this.sendRaw(
            toBinary(
                ServerWSPacketSchema,
                create(ServerWSPacketSchema, {
                    packet: {
                        case: "trackingEntry",
                        value: create(TrackingEntrySchema, e),
                    },
                })
            )
        );
    }

    public async sendFullUpdate() {
        if (process.env.DEBUG === "1") {
            console.log(`Prepare sending full update to ${this.connInfoStr}`);
        }

        if (this.isActive()) {
            // Get from database and then send
            const rawTrackingEntries = await db
                .selectFrom("arrival_departure_trackings")
                .where("is_active", "=", true)
                .innerJoin(
                    "vehicle_arrival_logs",
                    "vehicle_arrival_logs.id",
                    "arrival_departure_trackings.vehicle_arrival_log_id"
                )
                .innerJoin(
                    "vehicles",
                    "vehicles.id",
                    "vehicle_arrival_logs.vehicle_id"
                )
                .innerJoin(
                    "student_vehicle_mappings",
                    "student_vehicle_mappings.vehicle_id",
                    "vehicles.id"
                )
                .innerJoin(
                    "students",
                    "students.id",
                    "student_vehicle_mappings.student_id"
                )
                .select(["arrival_departure_trackings.id as tracking_id"])
                .select([
                    "arrival_departure_trackings.created_at as tracking_created_at",
                ])
                .selectAll()
                .execute();

            // Build the full tracking update
            const trackingEntryMap: Map<
                string,
                FullTrackingUpdate["trackingEntries"][number]
            > = new Map();
            const trackingEntries: FullTrackingUpdate["trackingEntries"] = [];

            for (const trackingEntry of rawTrackingEntries) {
                const trackingEntryId = trackingEntry.tracking_id;

                if (!trackingEntryMap.has(trackingEntryId)) {
                    const trackingEntryData: FullTrackingUpdate["trackingEntries"][number] =
                        {
                            $typeName: "app.v1.ws.TrackingEntry",
                            arrivalDepartureTrackingId: BigInt(
                                trackingEntry.tracking_id
                            ),
                            entryTimestampMs: BigInt(
                                trackingEntry.tracking_created_at!.getTime()
                            ),
                            isActive: true,
                            timeoutTimestampMs: BigInt(
                                trackingEntry.timeout_at!.getTime()
                            ),
                            vehicle: {
                                $typeName: "app.v1.ws.VehicleInformation",
                                id: BigInt(trackingEntry.vehicle_id),
                                licensePlate: trackingEntry.license_plate,
                            },
                            students: [],
                        };

                    trackingEntryMap.set(trackingEntryId, trackingEntryData);

                    trackingEntries.push(trackingEntryData);
                }

                trackingEntryMap.get(trackingEntryId)!.students.push(
                    create(StudentInformationSchema, {
                        id: BigInt(trackingEntry.student_id),
                        fullName: trackingEntry.full_name ?? "??",
                        callName: trackingEntry.call_name ?? "??",
                        class: trackingEntry.class ?? "??",
                    })
                );
            }

            if (this.isActive()) {
                if (process.env.DEBUG === "1") {
                    console.log(
                        `Sending full update (${trackingEntries.length} elements) to ${this.connInfoStr}`
                    );
                }
                const binaryData = toBinary(
                    ServerWSPacketSchema,
                    create(ServerWSPacketSchema, {
                        packet: {
                            case: "fullTrackingUpdate",
                            value: create(FullTrackingUpdateSchema, {
                                trackingEntries: trackingEntries.filter((e) =>
                                    this.filterTracking(e)
                                ),
                            }),
                        },
                    })
                );

                this.sendRaw(binaryData);

                const timeData = toBinary(ServerWSPacketSchema, {
                    $typeName: "app.v1.ws.ServerWSPacket",
                    packet: {
                        case: "serverTime",
                        value: {
                            $typeName: "app.v1.ws.ServerTime",
                            timestamp: BigInt(Date.now()),
                        },
                    },
                });

                this.sendRaw(timeData);
            } else {
                console.log(
                    `Client ${this.connInfoStr} became inactive during full update`
                );
            }
        }
    }

    protected onStudentDeparture(e: AppEventMap["studentDeparture"][0]) {
        console.log(`Sending student departure event to ${this.connInfoStr}`);
        this.sendRaw(
            toBinary(
                ServerWSPacketSchema,
                create(ServerWSPacketSchema, {
                    packet: {
                        case: "trackingEntry",
                        value: create(TrackingEntrySchema, e),
                    },
                })
            )
        );
    }

    protected onMessage(e: ClientWSPacket) {
        return;
    }

    protected onMessageRaw(
        e: MessageEvent<WSMessageReceive>,
        ws: WSContext<ServerWebSocket>
    ) {
        let data: ClientWSPacket;

        try {
            if (e.data instanceof ArrayBuffer || e.data instanceof Uint8Array) {
                data = fromBinary(ClientWSPacketSchema, new Uint8Array(e.data));
            } else if (typeof e.data === "string") {
                data = fromBinary(ClientWSPacketSchema, Buffer.from(e.data));
            } else {
                console.error(
                    `Failed to obtain packet type from ${this.connInfoStr}`
                );
                return;
            }
        } catch (e) {
            console.error(`Failed to decode packet from ${this.connInfoStr}`);
            return;
        }

        // Decode and reply to ping packets
        switch (data.packet.case) {
            case "pingPong":
                ws.send(
                    toBinary(ServerWSPacketSchema, {
                        $typeName: "app.v1.ws.ServerWSPacket",
                        packet: {
                            case: "pingPong",
                            value: {
                                $typeName: "app.v1.ws.PingPong",
                                type: PingPongType.PONG,
                            },
                        },
                    })
                );
                break;
            case undefined:
                return;
            default:
                this.onMessage(data);
        }
    }

    public sendRaw(b: Buffer | Uint8Array) {
        if (this.ws) {
            this.ws.send(b);
        } else {
            console.error(
                `Failed to send message to ${this.connInfoStr}, ws not initialized.`
            );

            this.cleanupFunction();
        }
    }

    protected onClose(e: CloseEvent, ws: WSContext<ServerWebSocket>) {
        console.log(`Connection closed from ${this.connInfoStr}`);
        for (const callback of this.attachedEventCallbacks) {
            callback();
        }
        this.ws = undefined;
        this.cleanupFunction();
    }

    /**
     * Disconnect the client from the server.
     */
    public drop() {
        if (this.ws) {
            this.ws.close(4001, "Connection dropped by server.");
        }
    }

    public isActive() {
        return this.ws !== undefined;
    }

    public getHandlers() {
        return {
            onOpen: this.onOpen.bind(this),
            onMessage: this.onMessageRaw.bind(this),
            onClose: this.onClose.bind(this),
        };
    }
}
