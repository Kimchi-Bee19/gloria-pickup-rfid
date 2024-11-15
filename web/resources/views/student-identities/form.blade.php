<x-mainlayout>
    <x-slot name="content">
        <!-- Form -->
        <section class="bg-white dark:bg-gray-900">
            <div class="px-4 mx-auto max-w-2xl lg:py-16">
                <!-- Title -->
                <h1 class="text-3xl text-dark-blue font-bold mb-5 text-center">{{ $title }}</h1>
                <form
                    action="{{ isset($student_identity)? route('student-identity.update', [ $student_identity->id]) : route('student-identity.insert') }}"
                    method="POST"
                >
                    @csrf
                    @if(isset($student_identity))
                        @method('put')
                    @else
                        @method('post')
                    @endif
                    <div class="grid gap-4 sm:grid-cols-2 sm:gap-6">
                        <!-- Tag ID Input -->
                        <div class="sm:col-span-2">
                            <label for="tag_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Tag
                                ID</label>
                            <input
                                type="text"
                                name="tag_id"
                                id="tag_id"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                placeholder="Tag ID"
                                value="{{ old('tag_id', isset($student_identity) ? $student_identity->tag_id : '') }}"
                                required=""
                            >
                            @error('tag_id')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        @include('includes.nfc-serial')
                        <!-- Notes Input -->
                        <div class="sm:col-span-2">
                            <label for="notes" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Notes</label>
                            <textarea
                                name="notes"
                                id="notes"
                                rows="8"
                                class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                placeholder="Your notes here"
                            >{{ old('notes', isset($student_identity) ? $student_identity->notes : '') }}</textarea>
                            @error('notes')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="flex justify-center space-x-4">
                        <button
                            type="submit"
                            class="inline-flex items-center px-5 py-2.5 mt-4 sm:mt-6 text-sm font-medium text-center text-white bg-blue-500 rounded-lg focus:ring-4 focus:ring-blue-200 dark:focus:ring-blue-900 hover:bg-blue-600"
                        >
                            {{ $button }}
                        </button>
                        <a href="/tag_siswa" class="inline-flex items-center px-5 py-2.5 mt-4 sm:mt-6 text-sm font-medium text-center text-white bg-gray-500 rounded-lg focus:ring-4 focus:ring-gray-200 dark:focus:ring-gray-900 hover:bg-gray-600">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </section>
    </x-slot>
</x-mainlayout>
