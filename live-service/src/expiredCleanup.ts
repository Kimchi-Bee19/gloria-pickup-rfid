import { appEvents } from "./appEvents";
import { db } from "./db";
import { TRACKING_TIMEOUT_SECONDS } from "./utils/constants";

function cleanupExpiredTrackingEntries() {
    const now = Date.now();
    // const expiryThreshold = new Date(now - TRACKING_TIMEOUT_SECONDS * 1000);

    db.updateTable("arrival_departure_trackings")
        .where("is_active", "=", true)
        .where("timeout_at", "<", new Date())
        .set({
            is_active: false,
        })
        .returningAll()
        .execute()
        .then((results) => {
            if (results.length !== 0) {
                console.log(
                    `Cleaned up ${results.length} expired tracking entries`
                );
            }

            // Send events for the expired entries
            results.forEach((result) => {
                appEvents.emit("trackingExpired", {
                    $typeName: "app.v1.ws.TrackingEntry",
                    arrivalDepartureTrackingId: BigInt(result.id),
                    isActive: false,
                    students: [],
                });
            });
        });
}

export function initCleanupExpiredTrackingEntries() {
    setInterval(cleanupExpiredTrackingEntries, 1000 * 10); // Run every 10 seconds
    cleanupExpiredTrackingEntries();
}
