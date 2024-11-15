import { Transaction } from "kysely";
import { db } from "./db";
import {
    AdminMarkDeparted,
    AdminPinTracking,
    AdminReorderTracking,
} from "./gen/ws_pb";
import { DB } from "kysely-codegen/dist/db";
import { TRACKING_TIMEOUT_SECONDS } from "./utils/constants";
import { compress } from "hono/compress";
import { except } from "hono/combine";
import { DatabaseError } from "pg";

/**
 * Function for admin to pin a tracking entry
 */
export async function setTrackingEntryPinned(message: AdminPinTracking) {
    const { trackingEntryId, isPinned } = message;

    return await db
        .updateTable("arrival_departure_trackings")
        .where("id", "=", trackingEntryId.toString())
        .where("is_active", "=", true)
        .set({
            is_pinned: isPinned,
        })
        .returning(["id", "is_pinned"])
        .executeTakeFirst();
}

/**
 * Function for admin to reorder a tracking entry
 */
export async function setTrackingEntryOrder(
    trx: Transaction<DB>,
    message: AdminReorderTracking
) {
    const { targetTrackingEntryId, relativeTo } = message;
    if (relativeTo.case === undefined) {
        console.error("Invalid relativeTo");
        return;
    }

    const defaultPositionModifier =
        relativeTo.case === "beforeTrackingEntryId" ? -1 : 1;

    // Find the relative to entry
    const existingPositions = await trx
        .selectFrom("arrival_departure_trackings as ad")
        .where("id", "=", relativeTo.value.toString())
        .select(({ eb }) => [
            "daily_absolute_position as anchor_position",
            eb
                .selectFrom("arrival_departure_trackings as ad1")
                .whereRef(
                    "ad1.daily_absolute_position",
                    relativeTo.case === "afterTrackingEntryId" ? ">" : "<",
                    "ad.daily_absolute_position"
                )
                .select("ad1.daily_absolute_position")
                .orderBy(
                    "ad1.daily_absolute_position",
                    relativeTo.case === "afterTrackingEntryId" ? "asc" : "desc"
                )
                .limit(1)
                .as("relative_position"),
        ])
        .executeTakeFirstOrThrow();

    // Calculate the new position
    const newPosition =
        (existingPositions.anchor_position +
            (existingPositions.relative_position ??
                existingPositions.anchor_position + defaultPositionModifier)) /
        2;

    // console.log(
    //     `anchor_position: ${existingPositions.anchor_position}, relative_position: ${existingPositions.relative_position}, new_position: ${newPosition}`
    // );

    // Update the tracking entry
    return await trx
        .updateTable("arrival_departure_trackings")
        .where("id", "=", targetTrackingEntryId.toString())
        .set({
            daily_absolute_position: newPosition,
        })
        .returning(["id", "daily_absolute_position"])
        .execute();
}

export async function getStudentInformationForTrackingEntry(
    trx: Transaction<DB>,
    trackingEntryId: string
) {
    return trx
        .selectFrom("student_vehicle_mappings as mappings")
        .innerJoin("students as s", "mappings.student_id", "s.id")
        .where("vehicle_id", "=", (eb) =>
            eb
                .selectFrom("vehicle_arrival_logs as va_logs")
                .where("id", "=", (eb) =>
                    eb
                        .selectFrom("arrival_departure_trackings as t")
                        .where("id", "=", trackingEntryId)
                        .select("t.vehicle_arrival_log_id")
                )
                .select("va_logs.vehicle_id")
        )
        .selectAll("s")
        .execute();
}

export type OnVehicleArrivalOptions =
    | OnVehicleArrivalOptionsManual
    | OnVehicleArrivalOptionsRfid;

interface OnVehicleArrivalOptionsManual {
    // Shouldn't be replaced, this will be stored in the database as well.
    type: "manual";
    vehicleId: string;
}

interface OnVehicleArrivalOptionsRfid {
    // Shouldn't be replaced, this will be stored in the database as well.
    type: "rfid";
    tagId: Buffer;
    authCheck?: Buffer;
}

export async function onVehicleArrival(
    trx: Transaction<DB>,
    options: OnVehicleArrivalOptions
) {
    const { type } = options;
    const arrivalTime = new Date();
    const timeoutTime = new Date(
        arrivalTime.getTime() + TRACKING_TIMEOUT_SECONDS * 1000
    );

    // Note: lock vehicles for no key update to prevent weird race conditions

    /**
     * 1. Find the vehicle
     * 2. If tracking entry already exists for this vehicle, then skip
     * 3. else, create a vehicle arrival log and a tracking entry.
     */

    let partialTargetVehicleQuery;

    if (type === "manual") {
        partialTargetVehicleQuery = trx
            .with("target_identity", (eb) =>
                eb.selectNoFrom((eb) => [eb.val(null).as("vehicle_id")])
            )
            .with("target_vehicle", (qb) =>
                qb
                    .selectFrom("vehicles")
                    .where("id", "=", options.vehicleId)
                    .select("id")
                    .forNoKeyUpdate()
            )
            .with("new_arrival_log", (qb) =>
                qb
                    .insertInto("vehicle_arrival_logs")
                    .values((eb) => ({
                        method: eb.val(type),
                        vehicle_id: eb
                            .selectFrom("target_vehicle")
                            .select("id"),
                        arrival_time: eb.val(arrivalTime),
                    }))
                    .returning("id")
            );
    } else {
        // RFID based, make a subquery to query the correct vehicle based on the tag data
        partialTargetVehicleQuery = trx
            .with("target_identity", (qb) =>
                qb
                    .selectFrom("vehicle_identities")
                    .where("tag_id", "=", options.tagId)
                    .where(
                        "auth_check",
                        options.authCheck === undefined ? "is" : "=",
                        options.authCheck ?? null
                    )
                    .select("vehicle_id")
                    .select("id")
                    .forNoKeyUpdate()
            )
            .with("target_vehicle", (qb) =>
                qb
                    .selectFrom("vehicles")
                    .where(({ eb, selectFrom }) =>
                        eb(
                            "id",
                            "=",
                            selectFrom("target_identity").select("vehicle_id")
                        )
                    )
                    .select("id")
                    .forNoKeyUpdate()
            )
            .with("new_arrival_log", (eb) =>
                eb
                    .insertInto("vehicle_arrival_logs")
                    .values((eb) => ({
                        method: eb.val(type),
                        vehicle_id: eb
                            .selectFrom("target_vehicle")
                            .select("id"),
                        arrival_time: eb.val(arrivalTime),
                        vehicle_identity_id: eb
                            .selectFrom("target_identity")
                            .select("id"),
                    }))
                    .returning("id")
            );
    }

    // Once done finding the vehicle and adding the relevant log, look for the relevant tracking entry
    // This is done this way because it'll automatically be rollbacked if a conflict occurs, much cleaner this way.
    const trackingInsertQuery = partialTargetVehicleQuery
        .insertInto("arrival_departure_trackings")
        .columns([
            "vehicle_arrival_log_id",
            "created_at",
            "updated_at",
            "timeout_at",
        ])
        .expression((eb) =>
            eb
                .selectFrom("target_vehicle")
                // Note to self: don't mix up the order, the column order must match the columns in the table
                .select(({ selectFrom }) =>
                    selectFrom("new_arrival_log")
                        .select("id")
                        .as("vehicle_arrival_log_id")
                )
                .select(({ val }) => val(arrivalTime).as("created_at"))
                .select(({ val }) => val(arrivalTime).as("updated_at"))
                .select(({ val }) => val(timeoutTime).as("timeout_at"))
                .where(({ eb, selectFrom }) =>
                    eb.not(
                        eb.exists(
                            selectFrom("arrival_departure_trackings")
                                .innerJoin(
                                    "vehicle_arrival_logs",
                                    "arrival_departure_trackings.vehicle_arrival_log_id",
                                    "vehicle_arrival_logs.id"
                                )
                                .where("is_active", "=", true)
                                .where("vehicle_id", "=", ({ eb }) =>
                                    eb.selectFrom("target_vehicle").select("id")
                                )
                        )
                    )
                )
        );

    // Decode the expected errors along the way, and rethrow them as more human readable errors
    try {
        // Return: undefined with no error thrown --> active tracking entry already exists
        return await trackingInsertQuery.returningAll().executeTakeFirst();
    } catch (e) {
        if (e instanceof DatabaseError) {
            if (
                e.message.includes(
                    'null value in column "vehicle_id" of relation "vehicle_arrival_logs" violates not-null constraint'
                )
            ) {
                throw new Error("Vehicle not found");
            }
        }

        console.log(e);
        return undefined;
    }
}

/**
 * Mark as departed
 * is_active may only be false if:
 * 1. timeout is exceeded
 * 2. all students associated with the vehicle have departed
 *
 * Possible cases (departure by student id):
 * 1. One student, one tracking entry --> student departs, then test for is_active condition
 * 2. One or more student, multiple tracking entries --> student departs, then test for is_active condition, set timeout to 30 seconds after now.
 *
 * Type student, reason manual: admin-initiated departure
 * Type student, reason identity: student tapped RFID tag
 * Type tracking, reason manual: admin-initiated departure
 * Type tracking, reason identity: this shouldn't be posible
 */
export async function markDeparted(
    trx: Transaction<DB>,
    type: "student" | "tracking",
    id: string,
    reason: "manual" | "identity",
    identityId?: string
) {
    if (reason === "identity") {
        if (identityId === undefined) {
            throw new Error("identityId is required for identity reason");
        }
    }

    if (type === "tracking" && reason === "identity") {
        throw new Error(
            "tracking cannot be marked as departed with identity reason"
        );
    }

    const departureTime = new Date();

    if (type === "tracking") {
        // Update the tracking entry, then add all the student departure as manual
        const trackingEntryData = await trx
            .updateTable("arrival_departure_trackings")
            .where("arrival_departure_trackings.id", "=", id)
            .set({
                is_active: false,
                updated_at: departureTime,
            })
            .innerJoin(
                "vehicle_arrival_logs",
                "arrival_departure_trackings.vehicle_arrival_log_id",
                "vehicle_arrival_logs.id"
            )
            .innerJoin(
                "vehicles",
                "vehicle_arrival_logs.vehicle_id",
                "vehicles.id"
            )
            .innerJoin(
                "student_vehicle_mappings",
                "vehicles.id",
                "student_vehicle_mappings.vehicle_id"
            )
            .innerJoin(
                "students",
                "student_vehicle_mappings.student_id",
                "students.id"
            )
            .returning(["student_id"])
            .returningAll(["students", "arrival_departure_trackings"])
            .execute();

        if (trackingEntryData.length === 0) {
            throw new Error(
                "Didn't find any matching students for the tracking entry"
            );
        }

        const firstData = trackingEntryData[0];
        const partialReturnedTrackingEntry = {
            id: firstData.id,
            is_active: firstData.is_active,
            updated_at: firstData.updated_at,
            timeout_at: firstData.timeout_at,
        };

        await trx
            .insertInto("student_departure_logs")
            .values(
                trackingEntryData.map((v) => ({
                    student_id: v.student_id,
                    departure_time: new Date(),
                    method: "manual",
                }))
            )
            .execute();

        return [partialReturnedTrackingEntry];
    } else if (type === "student") {
        // Find all tracking entries with this student
        const trackingDatum = await trx
            .selectFrom("students")
            .innerJoin(
                "student_vehicle_mappings",
                "student_vehicle_mappings.student_id",
                "students.id"
            )
            .innerJoin(
                "vehicles",
                "vehicles.id",
                "student_vehicle_mappings.vehicle_id"
            )
            .innerJoin(
                "vehicle_arrival_logs",
                "vehicle_arrival_logs.vehicle_id",
                "vehicles.id"
            )
            .innerJoin(
                "arrival_departure_trackings",
                "arrival_departure_trackings.vehicle_arrival_log_id",
                "vehicle_arrival_logs.id"
            )
            .select((eb) => [
                // Tracking ID
                "arrival_departure_trackings.id",

                // Count of how many students are associated with this tracking
                eb
                    .selectFrom("arrival_departure_trackings as ad1")
                    .whereRef("ad1.id", "=", "arrival_departure_trackings.id")
                    .innerJoin(
                        "vehicle_arrival_logs as va1",
                        "va1.id",
                        "ad1.vehicle_arrival_log_id"
                    )
                    .innerJoin("vehicles as v1", "v1.id", "va1.vehicle_id")
                    .innerJoin(
                        "student_vehicle_mappings as svm1",
                        "svm1.vehicle_id",
                        "v1.id"
                    )
                    .select(({ fn }) => fn.countAll().as("max_student_count"))
                    .as("max_student_count"),

                // Count how many student departures are already made for this tracking entry
                eb
                    .selectFrom(({ selectFrom }) =>
                        selectFrom("student_departure_logs as sd1")
                            .whereRef(
                                "sd1.arrival_departure_tracking_id",
                                "=",
                                "arrival_departure_trackings.id"
                            )
                            .select("sd1.student_id")
                            .distinct()
                            .as("student_ids")
                    )
                    .select(({ fn }) =>
                        fn.countAll().as("student_departure_log_count")
                    )
                    .as("student_departure_log_count"),

                // Check if this student already has a departure log in this tracking entry
                eb
                    .selectFrom("student_departure_logs as sd1")
                    .where("sd1.student_id", "=", id)
                    .whereRef(
                        "sd1.arrival_departure_tracking_id",
                        "=",
                        "arrival_departure_trackings.id"
                    )
                    .select(({ fn }) =>
                        fn.countAll().as("related_student_departure_log_count")
                    )
                    .as("related_student_departure_log_count"),
            ])
            .where("students.id", "=", id)
            .where("is_active", "=", true)
            .execute();

        if (trackingDatum.length === 0) {
            throw new Error("No tracking data found for student");
        }

        // For each tracking entry, add the student's departure log
        await trx
            .insertInto("student_departure_logs")
            .values(
                trackingDatum
                    .filter((v) => v.related_student_departure_log_count == 0)
                    .map((trackingData) => ({
                        student_id: id,
                        student_identity_id:
                            reason === "identity" ? identityId : null,
                        method: reason === "identity" ? "manual" : "rfid",
                        departure_time: new Date(),
                        arrival_departure_tracking_id: trackingData.id,
                    }))
            )
            .execute();

        // For each tracking entry, check the total number of entries, if it reaches the expected maximum, then update it to no longer active.
        // If the total number of entries is less than the expected maximum, then update it's timeout to 30 seconds from now.
        const trackingCompletedIds = trackingDatum
            .map((v) => {
                if (typeof v.max_student_count !== "string") return "";
                if (typeof v.student_departure_log_count !== "string")
                    return "";

                const maxStudentCount = parseInt(v.max_student_count);
                const studentDepartureLogCount = parseInt(
                    v.student_departure_log_count
                );

                if (
                    maxStudentCount === studentDepartureLogCount ||
                    (maxStudentCount === studentDepartureLogCount + 1 &&
                        v.related_student_departure_log_count == 0)
                ) {
                    return v.id;
                }

                return "";
            })
            .filter((v) => v.length > 0);

        const trackingNotCompletedIds = trackingDatum
            .map((v) => v.id)
            .filter((v) => trackingCompletedIds.indexOf(v) === -1);

        const updateQueries: Promise<
            {
                id: string;
                is_active: boolean;
                timeout_at: Date | null;
                updated_at: Date;
            }[]
        >[] = [];

        if (trackingCompletedIds.length > 0) {
            updateQueries.push(
                trx
                    .updateTable("arrival_departure_trackings")
                    .set({
                        is_active: false,
                        updated_at: new Date(),
                    })
                    .where("id", "in", trackingCompletedIds)
                    .returning(["id", "is_active", "updated_at", "timeout_at"])
                    .execute()
            );
        }

        if (trackingNotCompletedIds.length > 0) {
            updateQueries.push(
                trx
                    .updateTable("arrival_departure_trackings")
                    .set({
                        timeout_at: new Date(new Date().getTime() + 30000),
                        updated_at: new Date(),
                    })
                    .where("id", "in", trackingNotCompletedIds)
                    .returning(["id", "is_active", "updated_at", "timeout_at"])
                    .execute()
            );
        }

        const updateResults = await Promise.all(updateQueries);

        return updateResults.flat(2);
    }
}
