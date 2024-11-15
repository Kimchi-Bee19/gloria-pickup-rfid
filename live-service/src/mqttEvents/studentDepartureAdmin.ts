import { create, fromBinary } from "@bufbuild/protobuf";
import { AppEventHandler } from "../abstract/eventHandler";
import { StudentDepartureAdminMessageSchema } from "../gen/main_pb";
import { db } from "../db";
import { appEvents } from "../appEvents";
import { markDeparted } from "../operations";
import { TrackingEntry, TrackingEntrySchema } from "../gen/ws_pb";


export interface VehicleArrivalEvent {
    /** Timestamp */
    ts: number;

    /** Vehicle Identity */
    id: string;
}

export default {
    topic: "events/student_departure_admin",
    options: { qos: 2 },
    async onMessage(rawMessage: Buffer) {
        // Parse the message
        const m = fromBinary(StudentDepartureAdminMessageSchema, rawMessage);
        const id = m.id;

        if (process.env.DEBUG) {
            console.debug(
                `[events/student_departure] > ts:${m.timestamp} id:${id}`
            );
        }

        const trxResult = await db.transaction().execute(async (trx) => {
            // Fetch student and associated vehicles
            const student = await trx
                .selectFrom("student_identities")
                .innerJoin("students", "student_identities.student_id", "students.id")
                .where("students.id", "=", m.id.toString())
                .selectAll()
                .select("student_identities.id as student_identity_id")
                .select("students.id as student_id")
                .executeTakeFirst();

            if (!student) {
                console.error(`No student found for id: ${id}`);
                return;
            }

            return await markDeparted(
                trx,
                "student",
                student.student_id,
                "manual",
                student.student_identity_id
            );
        }).catch((e) => {
            console.error(`Failed to mark departed for student id: ${id}`);
            return;
        });

        if (!trxResult) {
            console.log(
                `Transaction failed or no tracking found for timestamp: ${m.timestamp}`
            );
            return;
        }

        trxResult.forEach((entry) => {
            // Broadcast events
            appEvents.emit(
                "studentDeparture",
                create(TrackingEntrySchema, {
                    arrivalDepartureTrackingId: BigInt(entry.id),
                    timeoutTimestampMs: BigInt(
                        entry.timeout_at?.getTime() ?? new Date().getTime()
                    ),
                    isActive: entry.is_active,
                    students: [],
                })
            );
        });
    },
} satisfies AppEventHandler;
