<script lang="ts">
    import { createEventDispatcher } from 'svelte';

    // Form fields
    let callName = '';
    let fullName = '';
    let studentClass = '';
    let licensePlate = '';
    let arrivalTime = new Date().toISOString(); // Capture the current time when form is opened

    // Dispatch event to parent when form is submitted
    const dispatch = createEventDispatcher();

    // Function to handle form submission -> temporary mock insert data cuz I'm still learning to use websocket or mqtt
    const handleSubmit = () => {
        // Ensure fields are filled before proceeding
        if (!callName || !fullName || !studentClass || !licensePlate) {
            console.error("All fields are required.");
            return;
        }

        const newEntry = {
            students: [
                {
                    id: Date.now().toString().split(' ')[0], 
                    fullName: fullName,
                    callName: callName, 
                    class: studentClass,
                },
            ],
            vehicle: {
                id: Date.now().toString().split(' ')[0], 
                licensePlate: licensePlate,
            },
            entryTimestampMs: Date.now(),
            isActive: true, // Default to active
        };

        // Dispatch the new entry to the parent component
        dispatch('addTrackingEntry', newEntry);

        // Clear form fields
        callName = '';
        fullName = '';
        studentClass = '';
        licensePlate = '';
        arrivalTime = new Date().toISOString();
    };
</script>

<!-- Form for adding a new student and vehicle entry -->
<div class="bg-blue-2 p-4 rounded-md text-white">
    <form on:submit|preventDefault={handleSubmit}>
        <div class="mb-4">
            <label for="student_name" class="block mb-2 text-lg">Student Nick Name</label>
            <input
                type="text"
                id="student_name"
                bind:value={callName}
                required
                class="p-2 rounded-md w-full text-black"
                placeholder="Enter student's name"
            />
        </div>
        <div class="mb-4">
            <label for="student_name" class="block mb-2 text-lg">Student Full Name</label>
            <input
                type="text"
                id="student_name"
                bind:value={fullName}
                required
                class="p-2 rounded-md w-full text-black"
                placeholder="Enter student's name"
            />
        </div>

        <div class="mb-4">
            <label for="student_class" class="block mb-2 text-lg">Student Class</label>
            <input
                type="text"
                id="student_class"
                bind:value={studentClass}
                required
                class="p-2 rounded-md w-full text-black"
                placeholder="Enter student's class"
            />
        </div>

        <div class="mb-4">
            <label for="license_plate" class="block mb-2 text-lg">License Plate</label>
            <input
                type="text"
                id="license_plate"
                bind:value={licensePlate}
                required
                class="p-2 rounded-md w-full text-black"
                placeholder="Enter vehicle's license plate"
            />
        </div>

        <!-- Hidden Arrival Time -->
        <input type="hidden" bind:value={arrivalTime} />

        <div class="flex justify-center">
            <button type="submit" class="bg-blue-3 hover:bg-blue-4 text-white p-2 rounded-md text-lg">
                Add Entry
            </button>
        </div>
    </form>
</div>
