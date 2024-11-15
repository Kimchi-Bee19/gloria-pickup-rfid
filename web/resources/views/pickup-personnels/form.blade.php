<x-mainlayout>
    <x-slot name="content">
        <!-- Form -->
        <section class="bg-white dark:bg-gray-900">
            <div class="px-4 mx-auto max-w-2xl lg:py-16">
                <!-- Title -->
                <h1 class="text-3xl text-dark-blue font-bold mb-5 text-center">{{ $title }}</h1>
                <form enctype="multipart/form-data"
                    action="{{ isset($pickup_personnel) ? route('pickup-personnel.update', [$pickup_personnel->id]) : route('pickup-personnel.insert') }}"
                    method="POST">
                    @csrf
                    @if (isset($pickup_personnel))
                        @method('put')
                    @else
                        @method('post')
                    @endif
                    <div class="grid gap-4 sm:grid-cols-2 sm:gap-6">
                        <!-- Full Name Input -->
                        <div class="sm:col-span-2">
                            <label for="full_name"
                                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nama
                                Lengkap</label>
                            <input type="text" name="full_name" id="full_name"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                placeholder="Nama Lengkap"
                                value="{{ old('full_name', isset($pickup_personnel) ? $pickup_personnel->full_name : '') }}"
                                required="">
                            @error('full_name')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <!-- Nomor Telepon -->
                        <div class="sm:col-span-2">
                            <label for="phone_number"
                                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">No Telp
                                Penjemput</label>
                            <input type="text" name="phone_number" id="phone_number"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                placeholder="No Telp"
                                value="{{ old('phone_number', isset($pickup_personnel) ? $pickup_personnel->phone_number : '') }}">
                            @error('phone_number')
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
                                value="{{ old('picture_url', isset($pickup_personnel) ? $pickup_personnel->picture_url : '') }}">
                            @error('picture_url')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <!-- Notes -->
                        <div class="sm:col-span-2">
                            <label for="notes" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Notes (Opsional)</label>
                            <textarea name="notes" id="notes"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                placeholder="Your notes here">{{ old('notes', isset($pickup_personnel) ? $pickup_personnel->notes : '') }}</textarea>
                            @error('notes')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class = "sm:col-span-2">
                            <div class = "block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                Students
                            </div>
                            <div class = "student-inputs">
                                @if (isset($pickup_personnel->student_pickup_personnel_mappings))
                                    @foreach ($pickup_personnel->student_pickup_personnel_mappings as $index => $this_student_pickup_personnel_mapping)
                                        <div class = "flex select-container mb-2">
                                            <select name="student_id[]"
                                                class="bg-gray-50 mr-2 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-[50%] p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                                                <option value="" disabled selected>Select a Student
                                                </option>
                                                @foreach ($students as $student)
                                                    <option value="{{ $student->id }}"
                                                        {{ $this_student_pickup_personnel_mapping->student_id == $student->id ? 'selected' : '' }}>
                                                        {{ $student->full_name . ' - ' . $student->class . ' - ' . $student->internal_id }}
                                                    </option>
                                                @endforeach
                                            </select>

                                            <select name="relationship_to_student[]"
                                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-[40%] p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                                                <option value="" disabled selected>Relation to student</option>
                                                <option value="ibu" {{ $this_student_pickup_personnel_mapping->relationship_to_student == 'ibu' ? 'selected' : '' }}>Ibu</option>
                                                <option value="ayah" {{ $this_student_pickup_personnel_mapping->relationship_to_student == 'ayah' ? 'selected' : '' }}>Ayah</option>
                                                <option value="kakek" {{ $this_student_pickup_personnel_mapping->relationship_to_student == 'kakek' ? 'selected' : '' }}>Kakek</option>
                                                <option value="nenek" {{ $this_student_pickup_personnel_mapping->relationship_to_student == 'nenek' ? 'selected' : '' }}>Nenek</option>
                                                <option value="wali" {{ $this_student_pickup_personnel_mapping->relationship_to_student == 'wali' ? 'selected' : '' }}>Wali</option>
                                            </select>
                                            <div
                                                class = "minus-student w-[10%] cursor-pointer {{ count($pickup_personnel->student_pickup_personnel_mappings) - 1 == $index ? '' : 'hidden' }} flex justify-center items-center">
                                                <i class="w-full text-center fa-solid fa-circle-minus"></i>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class = "flex select-container mb-2">
                                        <select name="student_id[]"
                                            class="mr-2 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-[50%] p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                                            <option value="" disabled selected>Select a Student
                                            </option>
                                            @foreach ($students as $student)
                                                <option value="{{ $student->id }}">
                                                    {{ $student->full_name . ' - ' . $student->class . ' - ' . $student->internal_id }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <select name="relationship_to_student[]"
                                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-[40%] p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                                            <option value="" disabled selected>Relation to student</option>
                                            <option value="ibu">Ibu</option>
                                            <option value="ayah">Ayah</option>
                                            <option value="kakek">Kakek</option>
                                            <option value="nenek">Nenek</option>
                                            <option value="wali">Wali</option>
                                        </select>
                                        <div
                                            class = "minus-student w-[10%] cursor-pointer hidden flex justify-center items-center">
                                            <i class="text-center text-dark-blue w-full fa-solid fa-circle-minus"></i>
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

                        <div id = "add-student"
                            class = "cursor-pointer w-fit bg-yellow-500 rounded-lg px-2 py-1 text-white hover:bg-yellow-700">
                            Tambah siswa
                        </div>
            
                    </div>

                    <div class="flex justify-center space-x-4">
                        <button type="submit"
                            class="inline-flex items-center px-5 py-2.5 mt-4 sm:mt-6 text-sm font-medium text-center text-white bg-blue-500 rounded-lg focus:ring-4 focus:ring-blue-200 dark:focus:ring-blue-900 hover:bg-blue-600">
                            {{ $button }}
                        </button>
                        <a href="/penjemput"
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
            $(document).ready(function() {
                $('#add-student').on('click', function() {
                    let studentContainer = $(this).prev();
                    studentContainer.find(".minus-student").addClass("hidden");
                    let studentInputsContainer = studentContainer.find(".student-inputs");
                    let newSelect = `
                                    <div class = "flex select-container mb-2">
                                        <select name="student_id[]"
                                            class="mr-2 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-[50%] p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                                            <option value="" disabled selected>Select a Student
                                            </option>
                                            @foreach ($students as $student)
                                                <option value="{{ $student->id }}">
                                                    {{ $student->full_name . ' - ' . $student->class . ' - ' . $student->internal_id }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <select name="relationship_to_student[]"
                                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-[40%] p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                                            <option value="" disabled selected>Relation to student</option>
                                            <option value="ibu">Ibu</option>
                                            <option value="ayah">Ayah</option>
                                            <option value="kakek">Kakek</option>
                                            <option value="nenek">Nenek</option>
                                            <option value="wali">Wali</option>
                                        </select>
                                        <div
                                            class = "cursor-pointer minus-student w-[10%] cursor-pointer flex justify-center items-center">
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

            });
        </script>
    </x-slot>
</x-mainlayout>
