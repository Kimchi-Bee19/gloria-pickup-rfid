<x-mainlayout>
    <x-slot name="custom_css">
        <!-- Tempus Dominus Styles -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@eonasdan/tempus-dominus@6.9.4/dist/css/tempus-dominus.min.css" crossorigin="anonymous">
    </x-slot>
    <x-slot name="content">
        <!-- Form -->
        <section class="bg-white dark:bg-gray-900">
            <div class="px-4 mx-auto max-w-2xl lg:py-16">
                <!-- Title -->
                <h1 class="text-3xl text-dark-blue font-bold mb-5 text-center">{{ $title }}</h1>
                <form enctype="multipart/form-data"
                    action="{{ isset($vehicle) ? route('vehicle.update', [$vehicle->id]) : route('vehicle.insert') }}"
                    method="POST">
                    @csrf
                    @if (isset($vehicle))
                        @method('put')
                    @else
                        @method('post')
                    @endif
                    <div class="grid gap-4 sm:grid-cols-2 sm:gap-6">
                        <!-- Type Input -->
                        <div class="sm:col-span-2">
                            <label for="type"
                                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Tipe
                                Kendaraan</label>
                            <input type="text" name="type" id="type"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                placeholder="Type" value="{{ old('type', isset($vehicle) ? $vehicle->type : '') }}"
                                required="">
                            @error('type')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <!-- Model Input -->
                        <div class="sm:col-span-2">
                            <label for="model"
                                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Model
                                Kendaraan</label>
                            <input type="text" name="model" id="model"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                placeholder="Model" value="{{ old('model', isset($vehicle) ? $vehicle->model : '') }}">
                            @error('model')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <!-- Color Input -->
                        <div class="sm:col-span-2">
                            <label for="color"
                                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Warna</label>
                            <input type="text" name="color" id="color"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                placeholder="Warna" value="{{ old('color', isset($vehicle) ? $vehicle->color : '') }}"
                                required="">
                            @error('color')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <!-- Plat Nomor Input -->
                        <div class="sm:col-span-2">
                            <label for="license_plate"
                                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Plat Nomor</label>
                            <input type="text" name="license_plate" id="license_plate"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                placeholder="Plat Nomor"
                                value="{{ old('license_plate', isset($vehicle) ? $vehicle->license_plate : '') }}"
                                required="">
                            @error('license_plate')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <!-- Tanggal Expiry Input -->
                        <div class="sm:col-span-2">
                            <label for="license_plate_expiry"
                                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Bulan
                                Expiry</label>
                          
                            <div class = "relative" id = "datetimepicker">
                                <input name="license_plate_expiry" id="license_plate_expiry"
                                class="cursor-pointer bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                value="{{ old('license_plate_expiry', isset($vehicle) ? explode('-', $vehicle->license_plate_expiry)[1]."/".explode('-', $vehicle->license_plate_expiry)[0] : '') }}"
                                placeholder="mm/yyyy"
                                >
                                <span class="absolute right-5 top-2">
                                    <i class="fas fa-calendar"></i>
                                </span>
                            </div>
                            
                            @error('license_plate_expiry')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <!-- Picture Input -->
                        <div class="sm:col-span-2">
                            <label for="picture_url"
                                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Gambar</label>
                            <input type="file" name="picture_url" id="picture_url"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                placeholder="URL Gambar"
                                value="{{ old('picture_url', isset($vehicle) ? $vehicle->picture_url : '') }}">
                            @error('picture_url')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        @if (isset($vehicle->identities) && !$vehicle->identities->isEmpty())
                            @foreach ($vehicle->identities as $this_identity)
                                <!-- RFID Tag Dropdown -->
                                <div class="sm:col-span-2 existing-tag-container">
                                    <label for="vehicle_identity_id"
                                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">RFID
                                        Tag</label>
                                    <select onchange="addNew(this);" name="vehicle_identity_id" id="vehicle_identity_id"
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                        >
                                        <option value="" disabled selected>Select an RFID Tag</option>
                                        
                                        @foreach ($vehicle_identities as $identity)
                                            <option value="{{ $identity->id }}"
                                                {{ $this_identity->id == $identity->id ? 'selected' : '' }}>
                                                {{ $identity->tag_id }}
                                            </option>
                                        @endforeach
                                        <option class = "bg-dark-blue text-white">Add New</option>
                                    </select>
                                    @error('vehicle_identity_id')
                                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            @endforeach
                        @else
                            <!-- RFID Tag Dropdown -->
                            <div class="sm:col-span-2 existing-tag-container">
                                <label for="vehicle_identity_id"
                                    class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">RFID
                                    Tag</label>
                                <select onchange="addNew(this);" name="vehicle_identity_id" id="vehicle_identity_id"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5"
                                    >
                                    <option value="" disabled selected>Select an RFID Tag</option>
                            
                                    @foreach ($vehicle_identities as $identity)
                                        <option value="{{ $identity->id }}">
                                            {{ $identity->tag_id }}
                                        </option>
                                    @endforeach
                                    <option class = "bg-dark-blue text-white">Add New</option>
                                </select>
                                @error('vehicle_identity_id')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif

                        <!--Create New RFID Tag-->
                        <div class="sm:col-span-2 hidden create-new-tag-container">
                            <label for="new_tag_id"
                                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">RFID
                                Tag (HEX)</label>
                            <input type = "text" name="new_tag_id"
                                class="new_tag_id mb-5 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                            @error('new_tag_id')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror

                            <label for="new_auth_check"
                                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">EPC (HEX)
                            </label>
                            <input type = "text" name="new_auth_check"
                                class="new_auth_check mb-5 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                            @error('new_auth_check')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror

            

                            <label for="new_notes"
                                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Notes
                            </label>
                            <textarea name="new_notes" rows="8"
                                class="new_notes block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                placeholder="Your notes here"></textarea>
                            @error('new_notes')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class = "sm:col-span-2">
                            <div
                                class = "block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                Students
                            </div>
                            <div class = "student-inputs">
                                @if (isset($vehicle->student_vehicle_mappings))
                                    @foreach ($vehicle->student_vehicle_mappings as $index => $this_student_vehicle_mapping)
                                        <div class = "flex select-container mb-2">
                                            <select name="student_id[]"
                                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-[90%] p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                                >
                                                <option value="" disabled selected>Select a Student
                                                </option>
                                                @foreach ($students as $student)
                                                    <option value="{{ $student->id }}"
                                                        {{ $this_student_vehicle_mapping->student_id == $student->id ? 'selected' : '' }}>
                                                        {{ $student->full_name . " - " . $student->class . " - " . $student->internal_id}} 
                                                    </option>
                                                @endforeach
                                            </select>
                                            
                                            <div
                                                class = "minus-student w-[10%] cursor-pointer {{(count($vehicle->student_vehicle_mappings)-1 == $index) ? '': 'hidden'}} flex justify-center items-center">
                                                <i class="w-full text-center fa-solid fa-circle-minus"></i>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class = "flex select-container mb-2">
                                        <select name="student_id[]"
                                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-[90%] p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                            >
                                            <option value="" disabled selected>Select a Student
                                            </option>
                                            @foreach ($students as $student)
                                                <option value="{{ $student->id }}">
                                                    {{ $student->full_name . " - " . $student->class . " - " . $student->internal_id}} 
                                                </option>
                                            @endforeach
                                        </select>
                                        <div
                                            class = "minus-student w-[10%] cursor-pointer hidden flex justify-center items-center">
                                            <i class="text-center w-full fa-solid fa-circle-minus"></i>
                                        </div>
                                    </div>
                                @endif
                                
                            </div>
                            @error('student_id')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                            @error('student_id.*')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                            @error('relationship_to_student')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                           
                        </div>
                        <div id = "add-student" class = "w-fit bg-yellow-500 rounded-lg px-2 py-1 text-white hover:bg-yellow-700">
                            Tambah siswa
                        </div>

                    </div>

                    <div class="flex justify-center space-x-4">
                        <button type="submit"
                            class="inline-flex items-center px-5 py-2.5 mt-4 sm:mt-6 text-sm font-medium text-center text-white bg-blue-500 rounded-lg focus:ring-4 focus:ring-blue-200 dark:focus:ring-blue-900 hover:bg-blue-600">
                            {{ $button }}
                        </button>
                        <a href="/kendaraan"
                            class="inline-flex items-center px-5 py-2.5 mt-4 sm:mt-6 text-sm font-medium text-center text-white bg-gray-500 rounded-lg focus:ring-4 focus:ring-gray-200 dark:focus:ring-gray-900 hover:bg-gray-600">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </section>
    </x-slot>

    <x-slot name="custom_js">
        <!-- Popperjs -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" crossorigin="anonymous"></script>
<!-- Tempus Dominus JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/@eonasdan/tempus-dominus@6.9.4/dist/js/tempus-dominus.min.js" crossorigin="anonymous"></script>
        <script>
           new tempusDominus.TempusDominus(document.querySelector("#datetimepicker"), {
                localization: {
                    format: 'MM/yyyy',
                },
                display: {
                    components: {
                        calendar: true,
                        date: false,
                        month: true,
                        year: true,
                        clock: false,
                        hours: false,
                        minutes: false,
                        seconds: false,
                    },
                },
            });

            function addNew(element) {
                let existingTagContainer = $(element).closest(".existing-tag-container");
                let createNewTagContainer = existingTagContainer.siblings(".create-new-tag-container");
                if ($(element).val().toLowerCase() == 'add new') {
                    createNewTagContainer.removeClass("hidden");
                } else {
                    createNewTagContainer.addClass("hidden");
                    createNewTagContainer.find(".new_tag_id").val(null);
                    createNewTagContainer.find(".new_notes").val(null);
                    createNewTagContainer.find(".new_auth_check").val(null);
                }
            }
            
            $(document).ready(function() {
                $('#add-student').on('click', function() {
                    let studentContainer = $(this).prev();
                    studentContainer.find(".minus-student").addClass("hidden");
                    let studentInputsContainer = studentContainer.find(".student-inputs");
                    let newSelect = `
                    <div class="flex select-container mb-2">
                        <select name="student_id[]"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-[90%] p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                >
                                <option value="" disabled selected>Select a Student</option>
                                @foreach ($students as $student)
                                    <option value="{{ $student->id }}">{{ $student->full_name . " - " . $student->class . " - " . $student->internal_id }}</option>
                                @endforeach
                        </select>
                        <div class="minus-student w-[10%] flex cursor-pointer justify-center items-center">
                                <i class="text-center w-full fa-solid fa-circle-minus"></i>
                        </div>
                    </div>
                    `;

                    studentInputsContainer.append(newSelect);
                });

                $(document).on('click', '.minus-student', function() {
                    thisSelectContainer = $(this).parent();
                    previousSelectContainer = thisSelectContainer.prev();
                    thisSelectContainer.remove();
                    previousSelectContainer.find(".minus-student").removeClass("hidden");
                });

                function loadTagInputs(tagCount) {
                    $(".tag-inputs").html("");
                    let newSelect = "";
                    for (let i = 1; i <= tagCount; i++) {
                        const minusTagClass = (i < tagCount || i == 1) ? 'hidden' : 'flex';
                        newSelect = `
                        <label for="tag_id_${i}"
                                class="block my-2 text-sm font-medium text-gray-900 dark:text-white">Tag ${i}</label>
                        <div class="flex">
                            <select name="tag_id[]" id="tag_id_${i}"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-[90%] p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                required>
                                <option value="" disabled selected>Select an RFID Tag</option>
                                @foreach ($vehicle_identities as $identity)
                                    <option value="{{ $identity->id }}">{{ $identity->tag_id }}</option>
                                @endforeach
                            </select>
                            <div class="minus-tag w-[10%] cursor-pointer ${minusTagClass} justify-center items-center">
                                <i class="fa-solid fa-circle-minus"></i>
                            </div>
                        </div>
                    `;
                        $(".tag-inputs").append(newSelect);
                    }
                }

            });
        </script>
    </x-slot>
</x-mainlayout>
