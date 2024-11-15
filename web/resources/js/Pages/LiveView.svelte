<script lang="ts">
	import Layout from "../Layout.svelte";
	import logo from "../../gloria-logo.png";
	import TrackingCard from "@/Components/TrackingCard.svelte";
	import { initializeLiveView, liveDisplayConfig, trackingData } from "@/live-view";
	import { fly } from "svelte/transition";
	import { quintOut } from "svelte/easing";
	import Unauthenticated from "@/Components/Unauthenticated.svelte";
	import { onMount } from "svelte";
	import FirstConnect from "@/Components/FirstConnect.svelte";
	import arrivalAudio from "@/../audio/arrival-notification.wav";
	import { liveMetrics } from "@/global-ws-store";
	

	onMount(initializeLiveView);
</script>

<svelte:head>
	<title>Live Antrean Penjemputan</title>
	<link rel="preload" href={arrivalAudio} as="fetch" />
</svelte:head>

<Layout>
	<main class="flex w-full h-screen flex-col">
		<section class="flex items-center w-full h-16 bg-blue-3 text-white gap-4 px-4 shadow">
			<div class="flex gap-4 items-center">
				<img class="w-16 h-16" src={logo} alt="logo" />
				<h1 class="text-4xl font-bold">
					{$liveDisplayConfig.title ?? "Antrean Penjemputan"}
				</h1>
			</div>
			<p class="ml-auto text-gray-400 flex justify-center items-center gap-2">
				{#if $liveMetrics.isConnected}
					<div class="bg-green-600 rounded-full h-2 w-2"></div>
					RTT: {$liveMetrics.rtt.toFixed(0)}ms
				{:else}
					<div class="bg-red-600 rounded-full h-2 w-2"></div>
					Disconnected
				{/if}
			</p>
			<p class="text-4xl font-bold">
				{$liveMetrics.time.getHours().toString().padStart(2, "0") +
					":" +
					$liveMetrics.time.getMinutes().toString().padStart(2, "0")}
			</p>
		</section>
		{#if $liveMetrics.isAuthenticated}
			<section class="grid grid-cols-3 gap-4 overflow-hidden w-full p-4">
				{#each $trackingData.filter((entry) => entry.isActive).toSorted((a, b) => (a.absolutePosition ?? 0) - (b.absolutePosition ?? 0)) as trackingEntry (trackingEntry.arrivalDepartureTrackingId)}
					<TrackingCard
						user={trackingEntry.students}
						licensePlate={trackingEntry.vehicle?.licensePlate}
						entryTimestampMs={Number(trackingEntry.entryTimestampMs)}
						timeoutTimestampMs={Number(trackingEntry.timeoutTimestampMs)}
						id={`card-${trackingEntry.arrivalDepartureTrackingId}`}
					/>
				{/each}
			</section>
			<section
				class="flex overflow-hidden z-10 items-center gap-4 bg-blue-3 fixed bottom-0 w-full h-16 shadow"
			>
				<div
					class="bg-red-1 font-semibold text-white h-full flex items-center px-4 text-3xl"
				>
					ARRIVAL
				</div>
				<div
					class="flex-1 font-semibold text-white h-full flex items-center text-3xl gap-2 text-nowrap overflow-hidden"
				>
					{#each $trackingData
						.filter((entry) => entry.isActive)
						.toReversed() as trackingEntry (trackingEntry.arrivalDepartureTrackingId)}
						<div
							id="card-{trackingEntry.arrivalDepartureTrackingId}"
							transition:fly={{
								delay: 250,
								duration: 300,
								x: 100,
								y: 500,
								opacity: 0.5,
								easing: quintOut
							}}
							class="bg-blue-1 text-black px-4 py-2"
						>
							{trackingEntry.vehicle?.licensePlate}
						</div>
					{/each}
				</div>
			</section>
		{:else if $liveMetrics.isFirstConnection}
			<FirstConnect />
		{:else}
			<Unauthenticated
				message={$liveMetrics.authIdentifier ?? ""}
				publicAuthIdentifier={$liveMetrics.publicAuthIdentifier ?? ""}
			/>
		{/if}
	</main>
</Layout>
