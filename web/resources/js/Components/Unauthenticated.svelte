<script lang="ts">
    export let message: string;
    export let publicAuthIdentifier: string | undefined = undefined;
</script>

<main class="flex w-full h-screen flex-col text-4xl text-center items-center justify-center">
    <div class="font-bold bg-red-400 p-4 text-6xl mb-4">Unauthenticated</div>
    {#if publicAuthIdentifier}
        <p class="mb-4">To continue, please authenticate this display in the admin panel.</p>
    {/if}
    <p class="mb-8">{message}</p>
    {#if publicAuthIdentifier}
        <p class="text-lg">Fingerprint: {publicAuthIdentifier}</p>
    {/if}
</main>
