import { vitePreprocess } from "@sveltejs/vite-plugin-svelte";
import delegateEvents from "svelte-preprocess-delegate-events/preprocess";

export default {
    // Consult https://svelte.dev/docs#compile-time-svelte-preprocess
    // for more information about preprocessors
    preprocess: [delegateEvents(), vitePreprocess()]
};
