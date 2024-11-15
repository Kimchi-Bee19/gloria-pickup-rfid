<x-mainlayout>
    <x-slot name="content">
        <!-- Form -->
        <section class="bg-white dark:bg-gray-900">
            <div class="px-4 mx-auto max-w-2xl lg:py-16">
                <!-- Title -->
                <h1 class="text-3xl text-dark-blue font-bold mb-5 text-center">{{ $title }}</h1>
                <form
                    enctype="multipart/form-data"
                    action="{{ isset($live_display) ? route('live-display.update', [$live_display->id]) : route('live-display.insert') }}"
                    method="POST"
                >
                    @csrf
                    @if (isset($live_display))
                        @method('put')
                    @else
                        @method('post')
                    @endif
                    <div class="grid gap-4 sm:grid-cols-2 sm:gap-6">
                        <!-- Label Input -->
                        <div class="sm:col-span-2">
                            <label
                                for="label"
                                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"
                            >
                                Label</label>
                            <input
                                type="text"
                                name="label"
                                id="label"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                placeholder="Label"
                                value="{{ old('label', isset($live_display) ? $live_display->label : '') }}"
                                required=""
                            >
                            @error('label')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Title Input -->
                        <div class="sm:col-span-2">
                            <label
                                for="title"
                                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"
                            >Title</label>
                            <input
                                type="text"
                                name="title"
                                id="title"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                placeholder="Title"
                                value="{{ old('title', isset($live_display) ? $live_display->title : '') }}"
                                required=""
                            >
                            @error('title')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Group Regex Filter Input -->
                        <div class="sm:col-span-2">
                            <label
                                for="group_regex_filter"
                                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"
                            >Tag Regex Filter</label>
                            <input
                                type="text"
                                name="group_regex_filter"
                                id="group_regex_filter"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                placeholder="Group regex filter"
                                value="{{ old('group_regex_filter', isset($live_display) ? $live_display->group_regex_filter : '') }}"
                            >
                            @error('group_regex_filter')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <!-- Class Regex Filter Input -->
                        <div class="sm:col-span-2">
                            <label
                                for="class_regex_filter"
                                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"
                            >Class Regex Filter</label>
                            <input
                                type="text" name="class_regex_filter" id="class_regex_filter"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                placeholder="Plat Nomor"
                                value="{{ old('class_regex_filter', isset($live_display) ? $live_display->class_regex_filter : '') }}"
                            >
                            @error('class_regex_filter')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <!-- Filter Mode -->
                        <div class="sm:col-span-2">
                            <label
                                for="filter_mode"
                                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"
                            >Filter Mode</label>
                            <select
                                name="filter_mode"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                            >
                                <option value="or" {{isset($live_display) && $live_display->filter_mode == 'or' ? 'selected': ''}}>
                                    or
                                </option>
                                <option value="and" {{isset($live_display) && $live_display->filter_mode == 'and' ? 'selected': ''}}>
                                    and
                                </option>
                            </select>
                            @error('filter_mode')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <!-- Is Enabled -->
                        <div class="sm:col-span-2">
                            <label
                                for="is_enabled"
                                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"
                            >Is Enabled?</label>
                            <select
                                name="is_enabled"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                            >
                                <option value="true" {{isset($live_display) && $live_display->is_enabled == true ? 'selected': ''}}>
                                    Yes
                                </option>
                                <option value="false" {{isset($live_display) && $live_display->is_enabled == false ? 'selected': ''}}>
                                    No
                                </option>
                            </select>
                            @error('is_enabled')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Fingerprint Input -->
                        <div class="sm:col-span-2">
                            <label
                                for="fingerprint"
                                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"
                            >Fingerprint</label>
                            <div
                                id="fingerprint"
                                class="bg-gray-50 text-gray-900 text-sm rounded-lg focus:ring-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                            >
                                {{isset($live_display) ? $live_display->fingerprint : ''}}
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-center space-x-4">
                        <button
                            type="submit"
                            class="inline-flex items-center px-5 py-2.5 mt-4 sm:mt-6 text-sm font-medium text-center text-white bg-blue-500 rounded-lg focus:ring-4 focus:ring-blue-200 dark:focus:ring-blue-900 hover:bg-blue-600"
                        >
                            {{ $button }}
                        </button>
                        <a
                            href="/live-display"
                            class="inline-flex items-center px-5 py-2.5 mt-4 sm:mt-6 text-sm font-medium text-center text-white bg-gray-500 rounded-lg focus:ring-4 focus:ring-gray-200 dark:focus:ring-gray-900 hover:bg-gray-600"
                        >
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </section>
    </x-slot>

    <x-slot name="custom_js">
        <script>
            $(document).ready(function () {

            });
        </script>
    </x-slot>
</x-mainlayout>
