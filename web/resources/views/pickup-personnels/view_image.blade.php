<div id="image-modal-{{ $pickup_personnel->id }}" tabindex="-1" class="hidden fixed top-0 left-0 right-0 bottom-0 z-50 flex items-center justify-center w-full p-4 overflow-x-hidden overflow-y-auto h-full bg-black bg-opacity-50">
    <div class="relative p-4 w-full max-w-md max-h-full">
        <!-- Modal content -->
        <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
            <!-- Close button -->
            <button type="button" class="absolute top-3 right-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="image-modal-{{ $pickup_personnel->id }}">
                <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                </svg>
                <span class="sr-only">Close modal</span>
            </button>
            <!-- Image -->
            <div class="p-4 md:p-5 text-center">
                @if ($pickup_personnel['picture_url'])
                    <img src="{{ $pickup_personnel['picture_url'] }}" alt="{{ $pickup_personnel['model'] }}" class="mx-auto rounded-lg shadow-lg">
                @else
                    <p class="text-gray-500 dark:text-gray-400">No image available for this pickup personnel.</p>
                @endif
            </div>
        </div>
    </div>
</div>
