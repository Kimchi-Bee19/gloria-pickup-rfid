import { Context } from "hono";
import jwt from "jsonwebtoken";
import { BaseLiveClient } from "./BaseLiveClient";
import { WSContext } from "hono/ws";
import { ServerWebSocket } from "bun";
import {
    ClientWSPacket,
    StudentInformationSchema,
    TrackingEntry,
    TrackingEntrySchema,
} from "../gen/ws_pb";
import {
    getStudentInformationForTrackingEntry,
    markDeparted,
    onVehicleArrival,
    setTrackingEntryOrder,
    setTrackingEntryPinned,
} from "../operations";
import { db } from "../db";
import { appEvents } from "../appEvents";
import { create } from "@bufbuild/protobuf";

export class AdminLiveClient extends BaseLiveClient {
    constructor(protected c: Context, protected cleanupFunction: () => void) {
        super(c, cleanupFunction);
    }

    protected override async onOpen(e: Event, ws: WSContext<ServerWebSocket>) {
        // Send metrics update
        await super.onOpen(e, ws);
        // Send full update
        await this.sendFullUpdate();
        await this.sendMetricsUpdate();
    }

    protected async sendMetricsUpdate() {
        // TODO: Send metrics update
    }

    protected async onMessage(e: ClientWSPacket) {
        const { case: type, value } = e.packet;
        if (type === "adminPinTracking") {
            console.log(
                `${this.connInfoStr}: ${value.trackingEntryId}, pin: ${value.isPinned}`
            );

            const updatedTrackingData = await setTrackingEntryPinned(
                e.packet.value
            );
            if (!updatedTrackingData) {
                console.log(
                    `${this.connInfoStr}: Server error > Tracking id not valid`
                );
            } else {
                // Broadcast modified tracking event
                appEvents.emit(
                    "trackingModified",
                    create(TrackingEntrySchema, {
                        ...updatedTrackingData,
                        students: [],
                    })
                );
            }
        } else if (type === "adminReorderTracking") {
            console.log(
                `${this.connInfoStr}: ${value.targetTrackingEntryId}, position: ${value.relativeTo.case}, anchor: ${value.relativeTo.value}`
            );

            const updatedTrackingData = db
                .transaction()
                .execute(async (trx) => {
                    return setTrackingEntryOrder(trx, value);
                });

            if (!updatedTrackingData) {
                console.log(
                    `${this.connInfoStr}: Server error > reorder attempt not valid`
                );
            } else {
                // Broadcast modified tracking event
                appEvents.emit(
                    "trackingModified",
                    create(TrackingEntrySchema, {
                        ...updatedTrackingData,
                        students: [],
                    })
                );
            }
        } else if (type === "adminManualArrival") {
            console.log(
                `${this.connInfoStr}: manual arrival vehicle id ${value.vehicleId}`
            );
            const trxData = await db.transaction().execute(async (trx) => {
                const trackingData = await onVehicleArrival(trx, {
                    type: "manual",
                    vehicleId: value.vehicleId.toString(),
                });

                if (!trackingData) throw new Error("Tracking entry not found");

                const students = await getStudentInformationForTrackingEntry(
                    trx,
                    trackingData.id
                );

                return {
                    trackingData,
                    students,
                };
            });
            if (!trxData) {
                console.log(
                    `${this.connInfoStr}: Server error > manual arrival attempt failed`
                );
            } else {
                // Broadcast modified tracking event
                appEvents.emit(
                    "vehicleArrival",
                    create(TrackingEntrySchema, {
                        ...trxData.trackingData,
                        students: trxData.students.map<
                            TrackingEntry["students"][number]
                        >((v) =>
                            create(StudentInformationSchema, {
                                id: BigInt(v.id),
                                callName: v.call_name ?? "Unknown",
                                class: v.class ?? "Unknown",
                                fullName: v.full_name ?? "Unknown",
                            })
                        ),
                    })
                );
            }
        } else if (type === "adminMarkDeparted") {
            console.log(
                `${this.connInfoStr}: mark departed tracking id ${value.departureType.case} for ${value.departureType.value}`
            );

            const departureType =
                value.departureType.case === "studentId"
                    ? "student"
                    : "tracking";
            const departureValue = value.departureType.value;

            if (departureValue === undefined) {
                console.log(
                    `${this.connInfoStr}: Server error > departure value not valid`
                );
                return;
            }

            const updateResults = await db
                .transaction()
                .execute(async (trx) => {
                    return await markDeparted(
                        trx,
                        departureType,
                        departureValue.toString(),
                        "manual"
                    );
                });

            if (!updateResults) {
                console.log(
                    `${this.connInfoStr}: Server error > mark departed attempt failed`
                );
                return;
            }

            // Broadcast all the updates
            for (const updateResult of updateResults) {
                appEvents.emit(
                    "trackingModified",
                    create(TrackingEntrySchema, {
                        ...updateResult,
                        students: [],
                    })
                );
            }
        }
    }

    protected async authenticate(): Promise<[boolean, string | null]> {
        const authToken = this.c.get("authToken");

        // Verify JWT Token
        try {
            const decoded = jwt.verify(
                authToken,
                Buffer.from(process.env.JWT_SECRET ?? "", "base64")
            );
            console.log("User id from JWT:", decoded.sub);

            return [true, null];
        } catch (e) {
            console.error(e);
            return [false, "Invalid JWT"];
        }
    }
}
