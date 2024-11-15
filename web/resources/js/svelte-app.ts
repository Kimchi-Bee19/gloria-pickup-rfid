import {createInertiaApp, type ResolvedComponent} from '@inertiajs/svelte'

createInertiaApp({
    resolve: name => {
        const pages = import.meta.glob('./Pages/**/*.svelte', {eager: true})
        return pages[`./Pages/${name}.svelte`] as ResolvedComponent
    },
    setup({el, App, props}) {
        new App({target: el!, props})
    },
})
