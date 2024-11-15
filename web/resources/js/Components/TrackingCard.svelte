<script lang="ts">
    import {onMount} from "svelte";
    import {slide} from 'svelte/transition';
    import {quintOut} from 'svelte/easing';

    interface ShortUserInfo {
        callName: string;
        class: string;
    }

    export let id: string;
    export let user: ShortUserInfo[] = [];
    export let licensePlate: string = "L 1728 DAL";
    export let entryTimestampMs: number = 1650000000;
    export let timeoutTimestampMs: number = 1650000000;

    $: entryDate = new Date(entryTimestampMs);
    $: entryTimestampString = `${entryDate.getHours().toString().padStart(2, "0")}:${entryDate.getMinutes().toString().padStart(2, "0")}:${entryDate.getSeconds().toString().padStart(2, "0")}`;

    let currentUserIndex = 0;
    $: callName = user[currentUserIndex]?.callName ?? "Unknown";
    $: userClass = user[currentUserIndex]?.class ?? "Unknown";

    let timeoutProgress = ((timeoutTimestampMs - Date.now()) / (timeoutTimestampMs - entryTimestampMs));

    onMount(() => {
        const intervalId = setInterval(() => {
            currentUserIndex = (currentUserIndex + 1) % user.length;
        }, 2000);

        const intervalId2 = setInterval(() => {
            timeoutProgress = ((timeoutTimestampMs - Date.now()) / (timeoutTimestampMs - entryTimestampMs));
            timeoutProgress = Math.max(0, timeoutProgress);
            timeoutProgress = Math.min(1, timeoutProgress);
        }, 100);

        return () => {
            clearInterval(intervalId);
            clearInterval(intervalId2);
        };
    });
</script>

<div
    id={id}
    transition:slide={{ duration: 300, easing: quintOut }}
    class="p-4 bg-blue-2 gap-2 text-white flex flex-col justify-center items-center"
>
    <h2 class="text-6xl font-bold text-center">{callName}</h2>
    <div class="flex gap-2 justify-center items-center h-1 w-full my-1">
        {#each Array(user.length) as _, i (i)}
            <div
                class="h-1 bg-blue-3 w-full rounded-lg transition-colors"
                class:bg-blue-3={i === currentUserIndex}
                class:bg-blue-1={i !== currentUserIndex}
            ></div>
        {/each}
    </div>
    <h2 class="text-xl">Arrived at {entryTimestampString}</h2>
    <div class="w-full bg-blue-1 rounded-full h-1 dark:bg-gray-700">
        <div class="bg-blue-3 h-1 rounded-full dark:bg-blue-500 transition-all" style="width: {timeoutProgress * 100}%"></div>
    </div>
    <div class="flex gap-4 justify-center items-center text-black text-xl w-full text-center">
        <div class="p-1 bg-blue-1 font-bold w-full">{userClass}</div>
        <div class="p-1 bg-blue-1 font-bold w-full">{licensePlate}</div>
    </div>
</div>
