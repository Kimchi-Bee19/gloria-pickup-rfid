import { customAlphabet } from "nanoid";
import { BaseLiveClient } from "./BaseLiveClient";
import { UnauthenticatedClientData, unauthenticatedClients } from "../ws";
import { Context } from "hono";
import { db } from "../db";
import { toBinary, create } from "@bufbuild/protobuf";
import {
    TrackingEntry,
    ServerWSPacketSchema,
    ClientConfigSchema,
    AuthenticatedClientConfigSchema,
} from "../gen/ws_pb";
import { ServerWebSocket } from "bun";
import { WSContext } from "hono/ws";

const hnano = customAlphabet("0123456789ABCDEF", 8);

export class DisplayLiveClient extends BaseLiveClient {
    private fingerprint: Uint8Array;
    public liveDisplayId: string;

    private groupRegexFilter: RegExp | null;
    private classRegexFilter: RegExp | null;
    private filterMode: "and" | "or";

    constructor(protected c: Context, protected cleanupFunction: () => void) {
        super(c, cleanupFunction);

        this.fingerprint = new Uint8Array();
        this.liveDisplayId = "";

        this.groupRegexFilter = null;
        this.classRegexFilter = null;
        this.filterMode = "or";
    }

    protected override filterTracking(trackingEntry: TrackingEntry) {
        if (this.groupRegexFilter && this.classRegexFilter) {
        } else if (this.groupRegexFilter !== null) {
            // TODO: Implement group regex filter
        } else if (this.classRegexFilter !== null) {
            const classRegexFilter = this.classRegexFilter;

            // Run regex filter
            const classNameMatches = trackingEntry.students
                .map((s) => s.class)
                .filter((c) => classRegexFilter.test(c));
            return classNameMatches.length > 0;
        }
        return true;
    }

    public async onModelUpdate() {
        await this.sendConfig();
        await this.sendFullUpdate();
    }

    protected override async onOpen(e: Event, ws: WSContext<ServerWebSocket>) {
        // Send config
        await super.onOpen(e, ws);

        if (this.liveDisplayId !== "") await this.sendConfig();

        // Send full update
        await this.sendFullUpdate();
    }

    public async sendConfig() {
        // Send configuration from the database.
        const config = await db
            .selectFrom("live_displays")
            .where("id", "=", this.liveDisplayId)
            .selectAll()
            .executeTakeFirst();

        if (!config) {
            console.error(
                `No live display found for id: ${this.liveDisplayId}`
            );
            this.drop();
            return;
        }

        // If not enabled, then drop and return
        if (config.is_enabled === false) {
            this.drop();
            return;
        }

        this.groupRegexFilter = config.group_regex_filter
            ? new RegExp(config.group_regex_filter)
            : null;
        this.classRegexFilter = config.class_regex_filter
            ? new RegExp(config.class_regex_filter)
            : null;
        this.filterMode = config.filter_mode as "and" | "or";

        this.sendRaw(
            toBinary(
                ServerWSPacketSchema,
                create(ServerWSPacketSchema, {
                    packet: {
                        case: "clientConfig",
                        value: create(ClientConfigSchema, {
                            config: {
                                case: "authenticated",
                                value: create(AuthenticatedClientConfigSchema, {
                                    clientLabel: config.label,
                                    title: config.title,
                                }),
                            },
                        }),
                    },
                })
            )
        );
    }

    protected async authenticate(): Promise<[boolean, string | null]> {
        const authToken = this.c.get("authToken");

        // Check the token in the cache
        const unauthClient = unauthenticatedClients.get(authToken) as
            | UnauthenticatedClientData
            | undefined;

        let fingerprint: Uint8Array;

        if (unauthClient) {
            fingerprint = unauthClient.fingerprint;
        } else {
            // Calculate its fingerprint, then lookup in the database
            const hasher = new Bun.CryptoHasher("sha256");
            hasher.update(authToken);
            fingerprint = hasher.digest();
        }

        const liveDisplay = await db
            .selectFrom("live_displays")
            .where("fingerprint", "=", Buffer.from(fingerprint))
            .selectAll()
            .executeTakeFirst();

        if (liveDisplay) {
            // Set the name then return true.
            this.fingerprint = fingerprint;
            this.liveDisplayId = liveDisplay.id;
            this.c.set("clientName", liveDisplay.label);

            this.connInfoStr = `${this.connInfo.remote.address} - ${liveDisplay.label}`;

            // Remove the unauthenticated client from the cache
            unauthenticatedClients.set(authToken, undefined, 0);

            // If is disabled, then return false
            if (liveDisplay.is_enabled === false) {
                return [false, "This display is disabled."];
            }

            return [true, null];
        } else {
            if (unauthClient) {
                unauthenticatedClients.set(authToken, unauthClient);
                return [false, `${unauthClient.humanReadableIdentifier}`];
            }
        }

        // Else, add it to the cache and return false.
        const hrid = hnano();
        unauthenticatedClients.set(authToken, {
            fingerprint,
            humanReadableIdentifier:
                hrid.substring(0, 4) + "-" + hrid.substring(4, 8),
        });

        return [false, hrid.substring(0, 4) + "-" + hrid.substring(4, 8)];
    }
}
