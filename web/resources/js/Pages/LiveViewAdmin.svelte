<script lang="ts">
    import Layout from "../Layout.svelte";
    import logo from "../../gloria-logo.png";
    import TrackingCardAdmin from "@/Components/TrackingCardAdmin.svelte";
	import FirstConnect from "@/Components/FirstConnect.svelte";
    import {liveMetrics} from "@/global-ws-store";
    import {initializeLiveView, pronouncePlate, trackingData} from "@/live-view-admin";
    import { get } from 'svelte/store';
    import {fly} from 'svelte/transition';
    import {quintOut} from 'svelte/easing';
    import Unauthenticated from "@/Components/Unauthenticated.svelte";
    import {onMount} from "svelte";
    import axios from 'axios';
	import { dndzone } from 'svelte-dnd-action'
    import {
        setTrackingEntryPinned,
        setTrackingEntryOrder,
        getStudentInformationForTrackingEntry
    } from

    // Get CSRF token from meta tag
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (csrfToken) {
        axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
    } else {
        console.warn("CSRF token not found. Make sure it's correctly set in the HTML.");
    }

    type LicensePlate = {
        id: string;
        license_plate: string;
    };

    type User = {
        callName: string;
        fullName: string;
        class: string;
    };

    type Vehicle = {
        licensePlate: string;
    };

    type TrackingDataEntry = {
        arrivalDepartureTrackingId: string;
        isActive: boolean;
        vehicle: Vehicle;
        students: User[];
        entryTimestampMs: number;
    };

    let loading: boolean = false;
    let message:string = "";
    let licensePlate: string = "";
    let selectedPlateId: string = "";
    let filteredPlates: LicensePlate[] = [];
    let licensePlates: LicensePlate[] = [];
    let connectedCount: number = 0;
    let disconnectedCount: number = 0;
    let items: { id: string; pinned: boolean; props: any }[] = [];

    async function fetchLicensePlates() {
        try {
            const response = await axios.get('/license-plates');
            licensePlates = response.data.sort();
            updateFilteredPlates();
            console.log("Fetched License Plates:", licensePlates);
        } catch (error) {
            console.error('Failed to fetch license plates:', error);
        }
    }

    async function fetchLiveDisplayStatus() {
        try {
            const response = await axios.get('/reader-status');
            connectedCount = response.data.connected;
            disconnectedCount = response.data.disconnected;
        } catch (error) {
            console.error('Failed to fetch live display status:', error);
        }
    }

    function updateFilteredPlates() {
        if (licensePlate) {
            filteredPlates = licensePlates.filter(plate =>
                plate.license_plate.toLowerCase().includes(licensePlate.toLowerCase())
            );
            if (filteredPlates.length > 0) {
                selectedPlateId = filteredPlates[0].id;
            } else {
                selectedPlateId = "";
            }
        } else {
            filteredPlates = licensePlates;
        }
    }

    const submitForm = async (event: Event) => {
        event.preventDefault();
        console.log("Submitting license plate ID:", selectedPlateId);

        loading = true;
        const data = { id: selectedPlateId };

        try {
            const response = await axios.post('/get-new-entry', data);
            console.log('Success:', response.data);
            message = "Vehicle added successfully!";
            licensePlate = "";
            selectedPlateId = "";
            updateFilteredPlates();
        } catch (error) {
            console.error('Submission error:', error);
            message = "Failed to submit license plate. Please try again.";
        } finally {
            loading = false;
        }
    };

    async function handleDepart(event: CustomEvent<{ vehicleId: string }>) {
        const { vehicleId } = event.detail;
        console.log(`Vehicle with ID ${vehicleId} has departed.`);
        const data = { id: vehicleId };
        try {
            const response = await axios.post('/mark-departed', data);
            console.log('Success:', response.data);
        } catch (error) {
            console.error('Submission error:', error);
        }
    }

    onMount(initializeLiveView);

    onMount(() => {
        fetchLicensePlates();
        updateFilteredPlates();
        fetchLiveDisplayStatus();
    });

    function togglePronouncePlate() {
        pronouncePlate.set(!get(pronouncePlate));
    }

	$: items = $trackingData.filter(entry => entry.isActive).map((entry) => ({
        id: entry.arrivalDepartureTrackingId.toString(),
        pinned: false,
        props: {
            user: entry.students,
            vehicle: entry.vehicle || undefined,
            entryTimestampMs: Number(entry.entryTimestampMs),
            id: `card-${entry.arrivalDepartureTrackingId}`
        }
    }));

    function togglePin(itemId: string) {
        const itemIndex = items.findIndex(i => i.id === itemId);
        if (itemIndex !== -1) {
            // Create a copy of the item with the updated pinned status
            const updatedItem = {
                ...items[itemIndex],
                pinned: !items[itemIndex].pinned
            };

            // Create a new array with the updated item
            items = [
                ...items.slice(0, itemIndex), 
                updatedItem, 
                ...items.slice(itemIndex + 1) 
            ];
        }
    }

    function moveItemUp(itemId: string) {
        const index = items.findIndex(item => item.id === itemId);
        if (index > 0) {
            const temp = items[index];
            items[index] = items[index - 1];
            items[index - 1] = temp;
        }
    }

    function moveItemDown(itemId: string) {
        const index = items.findIndex(item => item.id === itemId);
        if (index < items.length - 1) {
            const temp = items[index];
            items[index] = items[index + 1];
            items[index + 1] = temp;
        }
    }

    const flipDurationMs = 100;
    const handleConsider = (evt: CustomEvent<{ items: any[] }>) => {
        console.log("Consider event triggered:", evt.detail.items); // Log considered order
        items = evt.detail.items; // Update items array with new order
    };

    const handleFinalize = async (evt: CustomEvent<{ items: any[] }>) => {
        console.log("Finalize event triggered");

        // Get the original order and the new order of items
        const newOrder = evt.detail.items;

        // Find the moved item by comparing with the original items array
        const movedItem = newOrder.find(item => !items.includes(item)); // The item that's different

        if (movedItem) {
            // Find the index of the moved item in the original items array
            const originalIndex = items.findIndex(i => i.id === movedItem.id);

            // Log the ID before, current, and after the move
            const beforeItem = items[originalIndex - 1] || null;  // Item before the moved one
            const afterItem = items[originalIndex + 1] || null;   // Item after the moved one

            console.log(`Moved Item ID: ${movedItem.id}`);
            console.log(`Before: ${beforeItem ? beforeItem.id : 'None'}`);
            console.log(`Current: ${movedItem.id}`);
            console.log(`After: ${afterItem ? afterItem.id : 'None'}`);


            const data = { id: movedItem.id };
            try{
                const response = await axios.post('/change-order', data);
                console.log('Item move successfully processed:', response.data);
            } catch {
                console.error('Error sending item move data:', Error);
            }
        }

        // Update the items array with the new order
        items = newOrder;
    };

    // resize thing
    let columnWidths = [25, 25, 25, 25]; // Initial widths as percentages

    function resizeColumn(index: number, event: MouseEvent) {
        const startX = event.clientX;
        const startWidth = columnWidths[index];

        function onMouseMove(e: MouseEvent) {
            const deltaX = e.clientX - startX;
            const newWidth = Math.max(5, Math.min(100, startWidth + (deltaX / window.innerWidth) * 100));

            // Adjust columns based on the index being resized
            if (index === 0) { // First column
                columnWidths[0] = newWidth;
                columnWidths[1] = Math.max(0, 100 - newWidth - columnWidths[2] - columnWidths[3]);
            } else if (index === 1) { // Second column
                columnWidths[1] = newWidth;
                columnWidths[2] = Math.max(0, 100 - newWidth - columnWidths[0] - columnWidths[3]);
            } else if (index === 2) { // Third column
                columnWidths[2] = newWidth;
                columnWidths[3] = Math.max(0, 100 - newWidth - columnWidths[0] - columnWidths[1]);
            } else if (index === 3) { // Fourth column
                columnWidths[3] = newWidth;
                columnWidths[2] = Math.max(0, 100 - newWidth - columnWidths[0] - columnWidths[1]);
            }
        }

        function onMouseUp() {
            document.removeEventListener('mousemove', onMouseMove);
            document.removeEventListener('mouseup', onMouseUp);
        }

        document.addEventListener('mousemove', onMouseMove);
        document.addEventListener('mouseup', onMouseUp);
    }
    function formatTimestamp(timestamp: number): string {
        const date = new Date(timestamp);
        return date.toLocaleString().substring(11);
    }
    $: isPresetOpen = false;
    $: preset = 1;

    
</script>

<style>
    .containerr {
        display: flex;
        border: 1px solid #ccc;
    }

    .columnn {
        overflow: auto;
        border-right: 1px solid #ccc;
        position: relative;
    }

    .columnn:last-child {
        border-right: none; /* Remove border on the last column */
    }

    .resizer {
        width: 10px;
        cursor: ew-resize;
        background-color: #ccc;
        position: absolute;
        top: 0;
        right: 0;
        height: 100%;
        z-index: 1;
    }

    .loading-spinner {
        border: 4px solid rgba(255, 255, 255, 0.3);
        border-top: 4px solid #fff;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .pin-button {
        position: absolute;
        background: transparent;
        border: none;
        font-size: 1.25rem;
        cursor: pointer;
        margin-top: -0.5rem;
        right: 1.2rem;
    }
    .floatin_up {
        position: absolute;
        margin-top: 1rem;
        right: 1rem;
    }
    .floatin_down {
        position: absolute;
        margin-top: 3rem;
        right: 1rem;
    }
    .active {
        color: white;
        font-weight: bold;
    }
</style>

<svelte:head>
    <title>Admin Live Antrean Penjemputan</title>
</svelte:head>

<Layout>

    <main class="flex w-full h-screen flex-col">
        <section class="flex items-center justify-center w-full h-16 bg-blue-3 text-white gap-4 px-4 shadow">
            <div>
                <a href={route('dashboard')} class="gap-4 items-center justify-center block">
                    &lt; Return to Dashboard
                </a>
            </div>

            <div class="flex gap-4 items-center mx-auto">
                <img class="w-16 h-16" src={logo} alt="logo"/>
                <h1 class="text-4xl font-bold">Live View Admin</h1>
            </div>

            <div class="flex gap-4 items-center">
                <p class="text-gray-400 flex justify-center items-center gap-2">
                    {#if $liveMetrics.isConnected}
                        <div class="bg-green-600 rounded-full h-2 w-2"></div>
                        RTT: {$liveMetrics.rtt.toFixed(0)}ms
                    {:else}
                        <div class="bg-red-600 rounded-full h-2 w-2"></div>
                        Disconnected
                    {/if}
                </p>

                <p class="text-4xl font-bold">
                    {$liveMetrics.time.getHours().toString().padStart(2, "0") + ":" + $liveMetrics.time.getMinutes().toString().padStart(2, "0")}
                </p>
            </div>

        </section>
        <section class="flex items-center w-full h-8 bg-blue-2 text-white gap-4 px-4 shadow transition-colors">
            {#if $liveMetrics.isAuthenticated}
                <!-- <button
                    class="text-white px-2 hover:bg-white/50 h-full"
                >
                    Add New Entry
                </button> -->
                <button
                on:click={togglePronouncePlate}
                class="px-4 py-2 rounded text-white font-semibold
                    transition-colors duration-300"
                >
                

                <span class=" font-semibold {$pronouncePlate ? 'text-white' : 'text-gray-700'}">
                    Pronounce: {$pronouncePlate ? 'ON' : 'OFF'}
                </span>
                </button>
                <div class="flex items-center space-x-4">
                    {#each Array.from({ length: 3 }) as _, index}
                        <!-- <p>Loop iteration: {index + 1}</p> -->
                        <label class="inline-flex items-center cursor-pointer">
                            <input
                                type="radio"
                                name="preset"
                                value="{index + 1}"
                                bind:group={preset}
                                class="hidden"
                            />
                            <span class="px-4 py-2 rounded-l text-white transition-colors duration-300 font-semibold"
                                  class:active={preset === (index + 1)}>
                                Preset {index + 1}
                            </span>
                        </label>
                    {/each}
                    
                </div>
            {/if}
        </section>
        {#if $liveMetrics.isAuthenticated}
            <section class="gap-4 p-4 h-screen overflow-hidden containerr w-full">
                <div class="space-y-4 border-r border-gray-300 pr-6 max-h-full overflow-y-auto columnn" style="width: {columnWidths[0]}%; position: relative;"> <!-- Column 1 -->
                    <div class="resizer h-full" on:mousedown={event => resizeColumn(0, event)}></div>
                    <!-- <h1 class="text-4xl font-bold text-gray-700 mb-2">Status Reader</h1>
                    <h2 class="text-2xl font-semibold text-gray-700">Connected: {connectedCount}</h2>
                    <h2 class="text-2xl font-semibold text-gray-700">Disconnected: {disconnectedCount}</h2>
                    <h1 class="border-t-8 border-gray-300 w-full"></h1> -->
                    <h1 class="text-4xl font-bold text-gray-700 mb-2">Manual Entry</h1>
                    <form class="max-w-sm mx-auto" on:submit={submitForm}>
                        <label
                            for="licensePlate"
                            class="block mt-4 mb-2 text-sm font-medium text-gray-900 dark:text-white"
                        >Select License Plate</label>
                        <input
                            type="text"
                            id="licensePlate"
                            bind:value={licensePlate}
                            on:input={updateFilteredPlates}
                            placeholder="Type to search license plates"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-400 focus:border-blue-400 block w-full p-2.5"
                        />

                        <select bind:value={selectedPlateId}
                            on:change={() => {
                                const selectedPlate = filteredPlates.find(plate => plate.id === selectedPlateId);
                                licensePlate = selectedPlate ? selectedPlate.license_plate : "";
                            }}
                            class="mt-2 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-400 focus:border-blue-400 block w-full p-2.5">
                            <option value="" disabled>Select a License Plate</option>
                            {#each filteredPlates as plate}
                                <option value={plate.id}>{plate.license_plate}</option>
                            {/each}
                        </select>


                        <button
                            type="submit"
                            class="mt-4 w-full text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center"
                            disabled={loading}
                        >
                            {#if loading}
                                <div class="loading-spinner w-full"></div>
                            {:else}
                                Submit
                            {/if}
                        </button>

                        {#if message}
                            <div class="mt-4 text-green-600">{message}</div>
                        {/if}
                    </form>

                </div>
                <div class="space-y-4 border-r border-gray-300 pr-6 max-h-full overflow-y-auto columnn" style="width: {columnWidths[1]}%; position: relative;"> <!-- Column 2 -->
                    <div class="resizer h-full" on:mousedown={event => resizeColumn(1, event)}></div>
                    <h1 class="text-4xl font-bold text-gray-700 mb-2 text-center">Arrival</h1>
                    {#each $trackingData.filter(entry => entry.isActive).toReversed() as trackingEntry (trackingEntry.arrivalDepartureTrackingId)}
                        <div
                            id="card-{trackingEntry.arrivalDepartureTrackingId}"
                            transition:fly={{ delay: 250, duration: 300, x: 100, y: 500, opacity: 0.5, easing: quintOut }}
                            class="bg-dark-blue text-white px-4 py-2 items-center font-bold w-full"
                        >
                            {trackingEntry.vehicle.licensePlate}
                        </div>
                    {/each}
                </div>
                <div class="space-y-4 border-r border-gray-300 pr-6 max-h-full overflow-y-auto columnn" style="width: {columnWidths[2]}%; position: relative;"> <!-- Column 3 -->
                    <div class="resizer h-full" on:mousedown={event => resizeColumn(2, event)}></div>
                    <h1 class="text-4xl font-bold text-gray-700 mb-2 text-center">Tracking</h1>
                    {#each $trackingData.filter(entry => entry.isActive) as trackingEntry (trackingEntry.arrivalDepartureTrackingId)}
                        <TrackingCardAdmin on:depart={handleDepart}
                            user={trackingEntry.students}
                            vehicle={trackingEntry.vehicle}
                            entryTimestampMs={Number(trackingEntry.entryTimestampMs)}
                            id={`card-${trackingEntry.arrivalDepartureTrackingId}`}
                        />
                    {/each}
                </div>
                <div class="space-y-4 border-r border-gray-300 pr-6 max-h-full overflow-y-auto columnn" style="width: {columnWidths[3]}%; position: relative;"> <!-- Column 4 -->
                    <div class="container h-full">
                        <section
                            class="h-full overflow-scroll"
                            use:dndzone={{ items: items, flipDurationMs: flipDurationMs }}
                            on:consider="{handleConsider}"
                            on:finalize="{handleFinalize}"
                        >
                            {#each items.sort((a, b) => (b.pinned ? 1 : 0) - (a.pinned ? 1 : 0)) as item (item.id)}
                                <div class="bg-blue-2 border border-gray-300 rounded-lg p-4 mb-4 shadow-md relative">
                                    <button class="pin-button" on:click={() => togglePin(item.id)}>
                                        <span class={item.pinned ? 'opacity-100' : 'opacity-40'}>📌</span>
                                    </button>
                                    
                                    <button class="move-up-button bg-transparent floatin_up text-black hover:opacity-50 p-2 rounded" on:click={() => moveItemUp(item.id)}>
                                        <span class="text-xl">🔼</span> 
                                    </button>
                                    <button class="move-down-button bg-transparent floatin_down text-black hover:opacity-50 p-2 rounded" on:click={() => moveItemDown(item.id)}>
                                        <span class="text-xl">🔽</span> 
                                    </button>
                                    {#if preset==1}
                                        <div class="font-semibold text-gray-50 text-m">
                                            ID: {item.props.id}
                                        </div>
                                        <div class="text-lg text-white font-bold">
                                            License Plate: {item.props.vehicle?.licensePlate || "N/A"}
                                        </div>
                                        <div class="text-sm text-gray-100">
                                            Arrived At: {formatTimestamp(item.props.entryTimestampMs)}
                                        </div>
                                        <div class="text-gray-700">
                                            <div class="font-medium mb-1 text-white">Student Information:</div>
                                            <div class="grid grid-cols-[repeat(auto-fill,_minmax(200px,_1fr))] gap-4">
                                                {#each item.props.user as user}
                                                    <div class="pl-2 bg-gray-100 border border-gray-300 rounded-lg m-1">
                                                        <div>Call Name: <span class="font-bold">{user.callName}</span></div>
                                                        <div>Full Name: {user.fullName}</div>
                                                        <div>Class: <span class="font-bold">{user.class}</span></div>
                                                    </div>
                                                {/each}
                                            </div>
                                        </div>
                                        <button
                                            type="button"
                                            class="mt-2 w-full text-white bg-red-600 hover:bg-red-700 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center"
                                            on:click={() => handleDepart(new CustomEvent('depart', { detail: { vehicleId: String(item.props.vehicle.id) } }))}
                                            >
                                            Mark as Departed
                                        </button>
                                        {:else if preset == 2}
                                        <div class="text-5xl text-white font-bold">
                                            {item.props.vehicle?.licensePlate || "N/A"}
                                        </div>
                                        <button
                                            type="button"
                                            class="mt-2 w-5/6 text-white bg-red-600 hover:bg-red-700 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center"
                                            on:click={() => handleDepart(new CustomEvent('depart', { detail: { vehicleId: String(item.props.vehicle.id) } }))}
                                            >
                                            Mark as Departed
                                        </button>
                                        {:else if preset == 3}
                                        <div class="grid grid-cols-[repeat(auto-fill,_minmax(200px,_1fr))] w-5/6 gap-4">
                                            {#each item.props.user as user}
                                                <div class="pl-2 bg-gray-100 border border-gray-300 rounded-lg m-1">
                                                    <div>Call Name: <span class="font-bold">{user.callName}</span></div>
                                                    <div>Class: <span class="font-bold">{user.class}</span></div>
                                                </div>
                                            {/each}
                                        </div>
                                        <button
                                            type="button"
                                            class="mt-2 w-5/6 text-white bg-red-600 hover:bg-red-700 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center"
                                            on:click={() => handleDepart(new CustomEvent('depart', { detail: { vehicleId: String(item.props.vehicle.id) } }))}
                                            >
                                            Mark as Departed
                                        </button>
                                    {/if}
                                    
                                </div>
                            {/each}
                        </section>
                    </div>
                </div>
            </section>
        {:else if $liveMetrics.isFirstConnection}
			<FirstConnect />
        {:else}
            <Unauthenticated
                message="Session expired. Please re-authenticate."
            />
        {/if}
    </main>

</Layout>
