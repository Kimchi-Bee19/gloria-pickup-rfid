import { create, fromBinary } from "@bufbuild/protobuf";
import { AppEventHandler } from "../abstract/eventHandler";
import { db } from "../db";
import { VehicleArrivalMessageSchema } from "../gen/main_pb";
import { appEvents } from "../appEvents";
import { StudentInformationSchema } from "../gen/ws_pb";
import { TRACKING_TIMEOUT_SECONDS } from "../utils/constants";

export interface VehicleArrivalEvent {
    /** Timestamp */
    ts: number;

    /** Vehicle Identity */
    tagId: string;
}

export default {
    topic: "events/vehicle_arrival",
    options: { qos: 2 },
    async onMessage(rawMessage: Buffer) {
        // Parse the message.
        const m = fromBinary(VehicleArrivalMessageSchema, rawMessage);

        if (process.env.DEBUG) {
            console.debug(
                `[events/vehicle_arrival] > ts:${
                    m.timestamp
                } tagId:${Buffer.from(m.tagId).toString("hex")}`
            );
        }

        const trxResult = await db.transaction().execute(async (trx) => {
            const vehicle = await trx
                .selectFrom("vehicle_identities")
                .where("tag_id", "=", Buffer.from(m.tagId))
                .innerJoin(
                    "vehicles",
                    "vehicle_identities.vehicle_id",
                    "vehicles.id"
                )
                .selectAll()
                .select("vehicle_identities.id as vehicle_identity_id")
                .executeTakeFirst();

            if (!vehicle) {
                console.error(
                    `No vehicle found for tag id: ${Buffer.from(
                        m.tagId
                    ).toString("hex")}`
                );
                return;
            }

            const activeTracking = await trx
                .selectFrom("arrival_departure_trackings")
                .innerJoin(
                    "vehicle_arrival_logs",
                    "arrival_departure_trackings.vehicle_arrival_log_id",
                    "vehicle_arrival_logs.id"
                )
                .where("vehicle_id", "=", vehicle.id)
                .where("is_active", "=", true)
                .selectAll()
                .executeTakeFirst();

            if (activeTracking) {
                console.warn(
                    `Active tracking already exists for vehicle: ${vehicle.id}`
                );
                return;
            }

            const arrivalEntry = await trx
                .insertInto("vehicle_arrival_logs")
                .values({
                    vehicle_id: vehicle.vehicle_id ?? "",
                    vehicle_identity_id: vehicle.vehicle_identity_id,
                    arrival_time: new Date(Number(m.timestamp)),
                    method: "rfid",
                })
                .returning("id")
                .executeTakeFirst();

            if (!arrivalEntry) {
                console.error(
                    `Failed to create vehicle arrival log: ${m.timestamp}`
                );
                return;
            }

            const arrivalTracking = await trx
                .insertInto("arrival_departure_trackings")
                .values({
                    vehicle_arrival_log_id: arrivalEntry.id,
                    created_at: new Date(),
                    updated_at: new Date(),
                    timeout_at: new Date(new Date().getTime() + (TRACKING_TIMEOUT_SECONDS * 1000)),
                })
                .returningAll()
                .executeTakeFirst();

            if (!arrivalTracking) {
                console.error(
                    `Failed to create tracking entry for vehicle: ${vehicle.id}`
                );
                return;
            }

            const students = await trx
                .selectFrom("student_vehicle_mappings")
                .innerJoin(
                    "students",
                    "student_vehicle_mappings.student_id",
                    "students.id"
                )
                .where("vehicle_id", "=", vehicle.id)
                .selectAll()
                .execute();

            return {
                arrivalTrackingId: BigInt(arrivalTracking.id),
                entryTimestampMs: BigInt(arrivalTracking.created_at!.getTime()),
                vehicle: {
                    id: BigInt(vehicle.id),
                    licensePlate: vehicle.license_plate,
                    model: vehicle.model ?? "",
                    color: vehicle.color ?? "",
                    pictureUrl: vehicle.picture_url ?? "",
                },
                students: students.map((s) => ({
                    id: BigInt(s.id),
                    fullName: s.full_name ?? "??",
                    callName: s.call_name ?? "??",
                    class: s.class ?? "??",
                })),
            };
        });

        if (!trxResult) {
            console.log(`Transaction result is undefined`);
            return;
        }

        // Broadcast app event
        appEvents.emit("vehicleArrival", {
            $typeName: "app.v1.ws.TrackingEntry",
            arrivalDepartureTrackingId: trxResult.arrivalTrackingId,
            entryTimestampMs: trxResult.entryTimestampMs,
            isActive: true,
            timeoutTimestampMs:
                trxResult.entryTimestampMs +
                BigInt(TRACKING_TIMEOUT_SECONDS) * 1000n,
            vehicle: {
                $typeName: "app.v1.ws.VehicleInformation",
                ...trxResult.vehicle,
            },
            students: trxResult.students.map((v) =>
                create(StudentInformationSchema, v)
            ),
        });
    },
} satisfies AppEventHandler;
