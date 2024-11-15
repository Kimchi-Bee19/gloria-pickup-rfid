<x-mainlayout>
    <x-slot name="content">
        <!-- Form -->
        <section class="bg-white dark:bg-gray-900">
            <div class="px-4 mx-auto max-w-2xl lg:py-16">
                <!-- Title -->
                <h1 class="text-3xl text-dark-blue font-bold mb-5 text-center">{{ $title }}</h1>
                <form action="{{ isset($student_pickup_personnel_mapping) ? route('student-pickup-personnel-mapping.update', [$student_pickup_personnel_mapping->id]) : route('student-pickup-personnel-mapping.insert', [$pickup_personnel->id]) }}" method="POST">
                    @csrf
                    @if (isset($student_pickup_personnel_mapping))
                        @method('put')
                    @else
                        @method('post')
                    @endif
                    <div class="grid gap-4 sm:grid-cols-2 sm:gap-6">
                        <!-- Student Dropdown -->
                        <div class="sm:col-span-2">
                            <label for="student_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Student</label>
                            <select name="student_id" id="student_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" required>
                                <option value="" disabled selected>Select a Student</option>
                                @foreach($students as $student)
                                    <option value="{{ $student->id }}" {{ old('student_id', isset($student_pickup_personnel_mapping) && $student_pickup_personnel_mapping->student_id == $student->id ? 'selected' : '') }}>
                                        {{ $student->full_name . ' - ' . $student->class . ' - ' . $student->internal_id }}
                                    </option>
                                @endforeach
                            </select>
                            @error('student_id')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        @if (isset($student_pickup_personnel_mapping))
                            <div class="sm:col-span-2">
                                <select name="relationship_to_student" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                                    <option value="" disabled selected>Relation to student</option>
                                    <option value="ibu" {{ $student_pickup_personnel_mapping->relationship_to_student == 'ibu' ? 'selected' : '' }}>Ibu</option>
                                    <option value="ayah" {{ $student_pickup_personnel_mapping->relationship_to_student == 'ayah' ? 'selected' : '' }}>Ayah</option>
                                    <option value="kakek" {{ $student_pickup_personnel_mapping->relationship_to_student == 'kakek' ? 'selected' : '' }}>Kakek</option>
                                    <option value="nenek" {{ $student_pickup_personnel_mapping->relationship_to_student == 'nenek' ? 'selected' : '' }}>Nenek</option>
                                    <option value="wali" {{ $student_pickup_personnel_mapping->relationship_to_student == 'wali' ? 'selected' : '' }}>Wali</option>
                                </select>
                            </div>
                        @else
                            <div class="sm:col-span-2">
                                <label for="relationship_to_student" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Relationship to Student</label>
                                <select name="relationship_to_student" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                                    <option value="" disabled selected>Relation to student</option>
                                    <option value="ibu">Ibu</option>
                                    <option value="ayah">Ayah</option>
                                    <option value="kakek">Kakek</option>
                                    <option value="nenek">Nenek</option>
                                    <option value="wali">Wali</option>
                                </select>
                            </div>
                        @endif
                    </div>

                    <div class="flex justify-center space-x-4">
                        <button type="submit" class="inline-flex items-center px-5 py-2.5 mt-4 sm:mt-6 text-sm font-medium text-center text-white bg-blue-500 rounded-lg focus:ring-4 focus:ring-blue-200 dark:focus:ring-blue-900 hover:bg-blue-600">
                            {{ $button }}
                        </button>
                        <a href="/penjemput" class="inline-flex items-center px-5 py-2.5 mt-4 sm:mt-6 text-sm font-medium text-center text-white bg-gray-500 rounded-lg focus:ring-4 focus:ring-gray-200 dark:focus:ring-gray-900 hover:bg-gray-600">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </section>
    </x-slot>
</x-mainlayout>