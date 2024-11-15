<!--<script lang="ts">-->
<!--    import Layout from "../Layout.svelte";-->
<!--    import logo from "../../gloria-logo.png";-->
<!--    import TrackingCardAdmin from "@/Components/TrackingCardAdmin.svelte";-->
<!--    import TrackingForm from '@/Components/TrackingForm.svelte';-->
<!--    // import {configData, trackingDataAdmin} from "@/ws";-->
<!--    import {fly, slide} from 'svelte/transition';-->
<!--    import {quintOut} from 'svelte/easing';-->
<!--    import {onMount} from 'svelte';-->
<!--    import {writable} from "svelte/store";-->
<!--    import Unauthenticated from "@/Components/Unauthenticated.svelte";-->
<!--    import {getAuthData} from "@/ws-utils";-->

<!--    getAuthData("admin").then(token => {-->
<!--        if (token) {-->
<!--            console.log("Token:", token);-->
<!--        }-->
<!--    });-->

<!--    let activeCards = writable([]);-->
<!--    let showForm = false;-->

<!--    onMount(() => {-->
<!--        // Subscribe to trackingData updates-->
<!--        trackingDataAdmin.subscribe(newData => {-->
<!--            // Update only the active cards-->
<!--            activeCards.set(newData);-->

<!--            console.log('All trackingData entries:');-->
<!--            newData.forEach((entry, index) => {-->
<!--                console.log(`Entry ${index + 1}:`);-->
<!--                console.log('arrivalDepartureTrackingId:', entry.arrivalDepartureTrackingId);-->
<!--                console.log('isActive:', entry.isActive);-->
<!--                console.log('entryTimestampMs:', entry.entryTimestampMs);-->

<!--                if (entry.vehicle) {-->
<!--                    console.log('VehicleInformation:');-->
<!--                    console.log('  vehicleId:', entry.vehicle.id);-->
<!--                    console.log('  licensePlate:', entry.vehicle.licensePlate);-->
<!--                    console.log('  model:', entry.vehicle.model);-->
<!--                    console.log('  color:', entry.vehicle.color);-->
<!--                    console.log('  pictureUrl:', entry.vehicle.pictureUrl);-->
<!--                } else {-->
<!--                    console.log('VehicleInformation: None');-->
<!--                }-->

<!--                if (entry.students && entry.students.length > 0) {-->
<!--                    console.log('Students:');-->
<!--                    entry.students.forEach((student, studentIndex) => {-->
<!--                        console.log(`  Student ${studentIndex + 1}:`);-->
<!--                        console.log('    studentId:', student.id);-->
<!--                        console.log('    studentFullName:', student.fullName);-->
<!--                        console.log('    studentCallName:', student.callName);-->
<!--                        console.log('    class:', student.class);-->
<!--                        console.log('    pictureUrl:', student.pictureUrl);-->
<!--                    });-->
<!--                } else {-->
<!--                    console.log('Students: None');-->
<!--                }-->
<!--            });-->
<!--        });-->
<!--    });-->

<!--    // Handler for adding a new tracking entry-->
<!--    const handleAddTrackingEntryAdmin = (event) => {-->
<!--        const newEntry = event.detail;-->

<!--        // Update the trackingData store with the new entry-->
<!--        trackingDataAdmin.update((entries) => [...entries, newEntry]);-->
<!--        showForm = false; // Hide the form after submission-->
<!--    };-->

<!--    // Handler for marking an entry as departed-->
<!--    const handleMarkAsDeparted = (event) => {-->
<!--        const {vehicleId} = event.detail;-->

<!--        // Update the trackingDataAdmin store to remove the entry with the specified vehicleId-->
<!--        trackingDataAdmin.update((entries) =>-->
<!--            entries.filter(entry => entry.vehicle?.id !== vehicleId)-->
<!--        );-->
<!--    };-->

<!--    // Toggle the form's visibility-->
<!--    const toggleForm = () => {-->
<!--        showForm = !showForm;-->
<!--    };-->

<!--</script>-->

<!--<Layout>-->
<!--    <main class="flex w-full h-screen flex-col">-->
<!--        <section class="flex items-center justify-center w-full h-16 bg-blue-3 text-white gap-4 px-4 shadow">-->
<!--            <div>-->
<!--                <a href={route('dashboard')} class="gap-4 items-center justify-center block">-->
<!--                    &lt; Return to Dashboard-->
<!--                </a>-->
<!--            </div>-->

<!--            <div class="flex gap-4 items-center mx-auto">-->
<!--                <img class="w-16 h-16" src={logo} alt="logo"/>-->
<!--                <h1 class="text-4xl font-bold">Live View Admin</h1>-->
<!--            </div>-->

<!--            <div class="flex gap-4 items-center">-->
<!--                <p class="text-gray-400 flex justify-center items-center gap-2">-->
<!--                    {#if $configData.isConnected}-->
<!--                        <div class="bg-green-600 rounded-full h-2 w-2"></div>-->
<!--                        RTT: {$configData.rtt.toFixed(0)}ms-->
<!--                    {:else}-->
<!--                        <div class="bg-red-600 rounded-full h-2 w-2"></div>-->
<!--                        Disconnected-->
<!--                    {/if}-->
<!--                </p>-->

<!--                <p class="text-4xl font-bold">-->
<!--                    {$configData.time.getHours().toString().padStart(2, "0") + ":" + $configData.time.getMinutes().toString().padStart(2, "0")}-->
<!--                </p>-->
<!--            </div>-->

<!--        </section>-->
<!--        <section class="flex items-center w-full h-8 bg-blue-2 text-white gap-4 px-4 shadow transition-colors">-->
<!--            {#if $configData.isAuthenticated}-->
<!--                <button-->
<!--                    class="text-white px-2 hover:bg-white/50 h-full"-->
<!--                    on:click={toggleForm}-->
<!--                >-->
<!--                    {#if showForm}-->
<!--                        Close Form-->
<!--                    {/if}-->
<!--                    {#if !showForm}-->
<!--                        Add New Entry-->
<!--                    {/if}-->
<!--                </button>-->
<!--            {/if}-->
<!--        </section>-->

<!--        {#if $configData.isAuthenticated}-->

<!--            &lt;!&ndash; Hidden Tracking Form (Navbar-like from Right Side) &ndash;&gt;-->
<!--            <section class="relative">-->
<!--                {#if showForm}-->
<!--                    <div-->
<!--                        transition:slide={{ duration: 300, easing: quintOut }}-->
<!--                        class="fixed inset-y-0 right-0 w-80 bg-blue-2 p-4 z-50 shadow-md"-->
<!--                    >-->
<!--                        <TrackingForm on:addTrackingEntryAdmin={handleAddTrackingEntryAdmin}/>-->
<!--                    </div>-->
<!--                {/if}-->
<!--            </section>-->
<!--            <section class="grid grid-cols-4 gap-4 overflow-hidden w-full p-4">-->
<!--                {#each $trackingDataAdmin.filter(entry => entry.isActive) as trackingEntryAdmin (trackingEntryAdmin.arrivalDepartureTrackingId)}-->
<!--                    <TrackingCardAdmin-->
<!--                        user={trackingEntryAdmin.students}-->
<!--                        vehicle={trackingEntryAdmin.vehicle}-->
<!--                        entryTimestampMs={trackingEntryAdmin.entryTimestampMs ? Number(trackingEntryAdmin.entryTimestampMs) : 0}-->
<!--                        id={`card-${trackingEntryAdmin.arrivalDepartureTrackingId}`}-->
<!--                        on:depart={handleMarkAsDeparted}-->
<!--                    />-->
<!--                {/each}-->
<!--            </section>-->
<!--            <section class="flex overflow-hidden z-10 items-center gap-4 bg-blue-3 fixed bottom-0 w-full h-16 shadow">-->
<!--                <div class="bg-red-1 font-semibold text-white h-full flex items-center px-4 text-3xl">ARRIVAL</div>-->
<!--                <div class="flex-1 font-semibold text-white h-full flex items-center text-3xl gap-2 text-nowrap overflow-hidden">-->
<!--                    {#each $trackingDataAdmin.filter(entry => entry.isActive).toReversed() as trackingEntry (trackingEntry.arrivalDepartureTrackingId)}-->
<!--                        <div-->
<!--                            id="card-{trackingEntry.arrivalDepartureTrackingId}"-->
<!--                            transition:fly={{ delay: 250, duration: 300, x: 100, y: 500, opacity: 0.5, easing: quintOut }}-->
<!--                            class="bg-blue-1 text-black px-4 py-2"-->
<!--                        >-->
<!--                            {trackingEntry.vehicle?.licensePlate}-->
<!--                        </div>-->
<!--                    {/each}-->
<!--                </div>-->
<!--            </section>-->
<!--        {:else}-->
<!--            <Unauthenticated-->
<!--                message={$configData.authIdentifier ?? ""}-->
<!--                publicAuthIdentifier={$configData.publicAuthIdentifier}-->
<!--            />-->
<!--        {/if}-->
<!--    </main>-->
<!--</Layout>-->
