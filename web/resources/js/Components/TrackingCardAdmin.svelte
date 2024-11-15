<script lang="ts">
    import { onMount } from "svelte";
    import { slide } from 'svelte/transition';
    import { quintOut } from 'svelte/easing';
    import { createEventDispatcher } from 'svelte';

    interface UserInfo {
        id: BigInt,
        fullName: string,
        callName: string;
        class: string;
        pictureUrl?: string; // Added optional picture URL
    }

    interface Vehicle {
        id: BigInt,
        licensePlate: string,
        model: string,
        color: string,
        pictureUrl?: string; // Added optional picture URL
    }

    export let id: string;
    export let user: UserInfo[] = [];
    export let vehicle: Vehicle;
    export let entryTimestampMs: number = 1650000000;

    const dispatch = createEventDispatcher();

    $: entryDate = new Date(entryTimestampMs);
    $: entryTimestampString = `${entryDate.getHours().toString().padStart(2, "0")}:${entryDate.getMinutes().toString().padStart(2, "0")}:${entryDate.getSeconds().toString().padStart(2, "0")}`;

    let currentUserIndex = 0;
    $: currentUser = user[currentUserIndex] ?? { id: BigInt(0), callName: "Unknown", fullName: "Unknown", class: "Unknown" };


    onMount(() => {
        const intervalId = setInterval(() => {
            currentUserIndex = (currentUserIndex + 1) % user.length;
        }, 2000);
        return () => {
            clearInterval(intervalId);
        };
    });

    const markAsDeparted = () => {
        if (vehicle && vehicle.id) {
            const vehicleIdString = vehicle.id.toString();
            dispatch('depart', { vehicleId: vehicleIdString });
        } else {
            console.error('Invalid vehicle ID');
        }
    };
</script>

<div
    id="{id}"
    transition:slide={{ duration: 300, easing: quintOut }}
    class="p-4 bg-blue-2 text-white flex flex-col justify-center items-center"
>
    <div class="flex justify-between items-center w-full mb-4">
        <div class="flex flex-col">
            <h2 class="text-3xl font-bold text-left">
                <a href={currentUser.pictureUrl} target="_blank" rel="noopener noreferrer">
                    {currentUser.callName}
                </a>
            </h2>
            <h3 class="text-xl text-left">{currentUser.fullName}</h3>
        </div>
        <div class="text-2xl p-2 bg-blue-1 w-1/5  text-black font-bold text-center">
            {currentUser.class}
        </div>
    </div>

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

    <div class="flex flex-col items-start w-full">
        <div class="flex justify-between w-full text-xl">
            <div class="p-1 font-bold">Vehicle:</div>
            <div class="p-1 font-bold text-right">{vehicle?.color} | {vehicle?.model}</div>
        </div>
        <div class="flex justify-between w-full text-xl">
            <div class="p-1 font-bold">License:</div>
            <div class="p-1 font-bold text-right">{vehicle?.licensePlate}</div>
        </div>
    </div>

    <div class="flex gap-4 justify-center items-center text-black text-xl w-full text-center mt-4">
        <form action="">
            <button
                type="button"
                on:click={markAsDeparted}
                class="bg-green-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition-all duration-300 ease-in-out transform hover:scale-105 focus:outline-none focus:ring-4 focus:ring-blue-300"
            >
                Mark As Departed
            </button>
        </form>
    </div>
</div>
