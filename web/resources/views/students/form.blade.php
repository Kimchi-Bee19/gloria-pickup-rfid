<x-mainlayout>
    <x-slot name="content">
        <!-- Form -->
        <section class="bg-white dark:bg-gray-900">
            <div class="px-4 mx-auto max-w-2xl lg:py-16">
                <!-- Title -->
                <h1 class="text-3xl text-dark-blue font-bold mb-5 text-center">{{ $title }}</h1>
                <form enctype="multipart/form-data"
                    action="{{ isset($student) ? route('student.update', [$student->id]) : route('student.insert') }}"
                    method="POST">
                    @csrf
                    @if (isset($student))
                        @method('put')
                    @else
                        @method('post')
                    @endif
                    <div class="grid gap-4 sm:grid-cols-2 sm:gap-6">

                        <!-- No Induk Input -->
                        <div class="sm:col-span-2">
                            <label for="internal_id"
                                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">No Induk</label>
                            <input type="text" name="internal_id" id="internal_id"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                placeholder="Nama Lengkap"
                                value="{{ old('internal_id', isset($student) ? $student->internal_id : '') }}"
                                required="">
                            @error('internal_id')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Full Name Input -->
                        <div class="sm:col-span-2">
                            <label for="full_name"
                                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nama
                                Lengkap</label>
                            <input type="text" name="full_name" id="full_name"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                placeholder="Nama Lengkap"
                                value="{{ old('full_name', isset($student) ? $student->full_name : '') }}"
                                required="">
                            @error('full_name')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Call Name -->
                        <div class="sm:col-span-2">
                            <label for="call_name"
                                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nama
                                Panggilan</label>
                            <input type="text" name="call_name" id="call_name"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                placeholder="No Telp"
                                value="{{ old('call_name', isset($student) ? $student->call_name : '') }}">
                            @error('call_name')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Class -->
                        <div class="sm:col-span-2">
                            <label for="class"
                                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Kelas</label>
                            <input type="text" name="class" id="class"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                placeholder="No Telp"
                                value="{{ old('class', isset($student) ? $student->class : '') }}">
                            @error('class')
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
                                value="{{ old('picture_url', isset($student) ? $student->picture_url : '') }}">
                            @error('picture_url')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        @if (isset($student->identities) && !$student->identities->isEmpty())
                            @foreach ($student->identities as $this_identity)
                                <!-- RFID Tag Dropdown -->
                                <div class="sm:col-span-2 existing-tag-container">
                                    <label for="student_identity_id"
                                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">RFID
                                        Tag</label>
                                    <select onchange="addNew(this);" name="student_identity_id" id="student_identity_id"
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                                        <option value="" disabled selected>Select an RFID Tag</option>
                                        
                                        @foreach ($student_identities as $identity)
                                            <option value="{{ $identity->id }}"
                                                {{ $this_identity->id == $identity->id ? 'selected' : '' }}>
                                                {{ $identity->tag_id }}
                                            </option>
                                        @endforeach
                                        <option class = "bg-dark-blue text-white">Add New</option>
                                    </select>
                                    @error('student_identity_id')
                                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            @endforeach
                        @else
                            <!-- RFID Tag Dropdown -->
                            <div class="sm:col-span-2 existing-tag-container">
                                <label for="student_identity_id"
                                    class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">RFID
                                    Tag</label>
                                <select onchange="addNew(this);" name="student_identity_id" id="student_identity_id"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5">
                                    <option value="" disabled selected>Select an RFID Tag</option>
                                    
                                    @foreach ($student_identities as $identity)
                                        <option value="{{ $identity->id }}">
                                            {{ $identity->tag_id }}
                                        </option>
                                    @endforeach
                                    <option class = "bg-dark-blue text-white">Add New</option>
                                </select>
                                @error('student_identity_id')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif

                        <!--Create New RFID Tag-->
                        <div class="sm:col-span-2 hidden create-new-tag-container">
                            <label for="new_tag_id"
                                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">RFID
                                Tag</label>
                            <input type = "text" name="new_tag_id"
                                class="new_tag_id bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                            @error('new_tag_id')
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
                    </div>
                    <div class="flex justify-center space-x-4">
                        <button type="submit"
                            class="inline-flex items-center px-5 py-2.5 mt-4 sm:mt-6 text-sm font-medium text-center text-white bg-blue-500 rounded-lg focus:ring-4 focus:ring-blue-200 dark:focus:ring-blue-900 hover:bg-blue-600">
                            {{ $button }}
                        </button>
                        <a href="/siswa"
                            class="inline-flex items-center px-5 py-2.5 mt-4 sm:mt-6 text-sm font-medium text-center text-white bg-gray-500 rounded-lg focus:ring-4 focus:ring-gray-200 dark:focus:ring-gray-900 hover:bg-gray-600">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </section>
    </x-slot>
    <x-slot name="custom_js">
        <script>
             function addNew(element) {
                let existingTagContainer = $(element).closest(".existing-tag-container");
                let createNewTagContainer = existingTagContainer.siblings(".create-new-tag-container");
                if ($(element).val().toLowerCase() == 'add new') {
                    createNewTagContainer.removeClass("hidden");
                } else {
                    createNewTagContainer.addClass("hidden");
                    createNewTagContainer.find(".new_tag_id").val(null);
                    createNewTagContainer.find(".new_notes").val(null);
                }
            }
        </script>

    </x-slot>
</x-mainlayout>


