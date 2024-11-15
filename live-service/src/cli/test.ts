import { db } from "../db";
import { markDeparted, onVehicleArrival, setTrackingEntryOrder } from "../operations";

db.transaction().execute(async (trx) => {
    const r = await onVehicleArrival(trx, {
        type: "rfid",
        // vehicleId: "1",
        tagId: Buffer.from("c34b9981b0f423", "hex"),
        authCheck: Buffer.from("00", "hex"),
    });
    console.log(r);
    return r;
});
