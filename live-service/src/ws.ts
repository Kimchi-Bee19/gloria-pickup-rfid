import type { ServerWebSocket } from "bun";
import { Hono } from "hono";
import { createBunWebSocket } from "hono/bun";
import NodeCache from "node-cache";
import { AdminLiveClient } from "./liveClient/AdminLiveClient";
import { BaseLiveClient } from "./liveClient/BaseLiveClient";
import { DisplayLiveClient } from "./liveClient/DisplayLiveClient";

const { upgradeWebSocket, websocket } = createBunWebSocket<ServerWebSocket>();

interface Variables {
    clientName: string;
    authType: "token" | "jwt";
    authToken: string;
}

const app = new Hono<{ Variables: Variables }>();
export const connectedClients: Set<BaseLiveClient> = new Set();
export const unauthenticatedClients = new NodeCache({
    stdTTL: 60,
    checkperiod: 30,
});

export interface UnauthenticatedClientData {
    fingerprint: Uint8Array;
    humanReadableIdentifier: string;
}

// Minimum and maximum intervals for full refreshes, used to stagger the refreshes
const MINIMUM_FULL_REFRESH_INTERVAL_MS = 60 * 1000;
const MAXIMUM_FULL_REFRESH_INTERVAL_MS = 90 * 1000;

// Run the checking every 30 seconds
setInterval(() => {
    const now = Date.now();
    for (const client of connectedClients) {
        if (now - client.lastFullRefreshScheduledMs > 0) {
            const randomIntervalMs = Math.floor(
                Math.random() *
                    (MAXIMUM_FULL_REFRESH_INTERVAL_MS -
                        MINIMUM_FULL_REFRESH_INTERVAL_MS) +
                    MINIMUM_FULL_REFRESH_INTERVAL_MS
            );

            client.lastFullRefreshScheduledMs = now + randomIntervalMs;

            setTimeout(async () => {
                client.sendFullUpdate();
            }, randomIntervalMs);
        }
    }
});

app.use(async (c, next) => {
    c.set("clientName", "unknown");
    // Obtain the Websocket Protocol Header
    const authValue = c.req.header("sec-websocket-protocol");

    if (!authValue) {
        return c.json(
            {
                error: "Auth value not supplied",
            },
            403
        );
    }

    // Set the values
    const splitLocation = authValue?.indexOf(",") ?? -1;
    if (!splitLocation) {
        return c.json(
            {
                error: "Invalid auth value",
            },
            403
        );
    }

    const authType = authValue.substring(0, splitLocation);
    if (authType !== "token" && authType !== "jwt") {
        return c.json(
            {
                error: "Invalid auth type",
            },
            403
        );
    }

    c.set("authType", authType);
    c.set("authToken", authValue.substring(splitLocation + 2));

    await next();
});

app.get(
    "/",
    upgradeWebSocket((c) => {
        // Authenticate according to their authType
        let client: BaseLiveClient;

        switch (c.get("authType")) {
            case "token":
                client = new DisplayLiveClient(c, () => {
                    console.log(
                        "Removing client from connectedClients",
                        client.connInfoStr
                    );
                    connectedClients.delete(client);
                });
                break;
            case "jwt":
                client = new AdminLiveClient(c, () => {
                    console.log(
                        "Removing client from connectedClients",
                        client.connInfoStr
                    );
                    connectedClients.delete(client);
                });
                break;
            default:
                return {};
        }

        connectedClients.add(client);
        return client.getHandlers();
    })
);

export default { wsApp: app, websocket: websocket };
