import { create, fromBinary } from "@bufbuild/protobuf";
import { AppEventHandler } from "../abstract/eventHandler";
import { StudentDepartureMessageSchema } from "../gen/main_pb";
import { db } from "../db";
import { appEvents } from "../appEvents";
import { markDeparted } from "../operations";
import { TrackingEntry, TrackingEntrySchema } from "../gen/ws_pb";

export default {
    topic: "events/student_departure",
    options: { qos: 2 },
    async onMessage(rawMessage: Buffer) {
        // Parse the message
        const m = fromBinary(StudentDepartureMessageSchema, rawMessage);
        const tagIdHex = Buffer.from(m.tagId).toString("hex");

        if (process.env.DEBUG) {
            console.debug(
                `[events/student_departure] > ts:${m.timestamp} tagId:${tagIdHex}`
            );
        }

        const trxResult = await db.transaction().execute(async (trx) => {
            // Fetch student and associated vehicles
            const student = await trx
                .selectFrom("student_identities")
                .where("tag_id", "=", Buffer.from(m.tagId))
                .innerJoin(
                    "students",
                    "student_identities.student_id",
                    "students.id"
                )
                .selectAll()
                .select("student_identities.id as student_identity_id")
                .select("students.id as student_id")
                .executeTakeFirst();

            if (!student) {
                console.error(`No student found for tag id: ${tagIdHex}`);
                return;
            }

            return await markDeparted(
                trx,
                "student",
                student.student_id,
                "identity",
                student.student_identity_id
            );
        }).catch((e) => {
            console.error(`Failed to mark departed for student tag id: ${tagIdHex}`);
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
