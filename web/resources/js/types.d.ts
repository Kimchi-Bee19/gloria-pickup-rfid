import type {Axios} from "axios";
import type Alpine from "alpinejs";

declare global {
    interface Window {
        axios: Axios;
        Alpine: Alpine;
        Ziggy: ziggy.Ziggy;
    }
}

import { route as routeFn } from 'ziggy-js';

declare global {
    const route: typeof routeFn;
}
