<div id="assign-vehicle-popup" tabindex="-1"
    class="hidden fixed top-0 left-0 right-0 bottom-0 z-50 flex items-center justify-center w-full p-4 overflow-x-hidden overflow-y-auto h-full bg-black bg-opacity-50">
    <!-- Form -->
    <div class="bg-white w-full max-w-md relative rounded-lg shadow dark:bg-gray-700">
        <div class="p-8 mx-auto max-w-2xl">
            <!-- Title -->
            <h1 class="title text-3xl text-dark-blue font-bold mb-5 text-center"><!--Will be set in ajax response--></h1>
            <form class = "assign-identity" action="" method="POST">
                @csrf
                @method('put')
                <div class="grid gap-4 sm:grid-cols-2 sm:gap-6">
                    <!-- Selected vehicle Information -->
                    <div class="sm:col-span-2">
                        <label for="vehicle_id"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Vehicle</label>
                        <input type = "text" disabled name="vehicle_id" id="vehicle_id"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                    </div>
                    <!-- Existing RFID Tag Dropdown  -->
                    <div class="sm:col-span-2">
                        <label for="tag_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">RFID
                            Tag</label>
                        <select onchange="addNew(this);" name="tag_id"
                            class="tag_id bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                            <!--Will be populated in ajax response-->
                        </select>
                        <p class="error-container text-sm text-red-600 mt-1">
                        </p>
                    </div>
                    <!--Create New RFID Tag-->
                    <div class="sm:col-span-2 hidden create-new-tag-container">
                        <label for="new_tag_id"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">RFID
                            Tag (HEX)</label>
                        <input type = "text" name="new_tag_id"
                            class="new_tag_id mb-5 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                        <p class="error-container text-sm text-red-600 mt-1">
                        </p>

                        <label for="new_auth_check"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">EPC (HEX)
                        </label>
                        <input type = "text" name="new_auth_check"
                            class="new_auth_check mb-5 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                        <p class="error-container text-sm text-red-600 mt-1">
                        </p>
                            
                        <label for="new_notes"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Notes
                        </label>
                        <textarea name="new_notes" rows="8"
                            class="new_notes block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                            placeholder="Your notes here"></textarea>
                        <p class="error-container text-sm text-red-600 mt-1">
                        </p>
                    </div>
                </div>
                <div class="flex justify-center space-x-4 mt-10">
                    <button type = "submit"
                        class="cursor-pointer submit-identity inline-flex items-center px-5 py-2.5 mt-1 text-sm font-medium text-center text-white bg-blue-500 rounded-lg focus:ring-4 focus:ring-blue-200 dark:focus:ring-blue-900 hover:bg-blue-600">
                        Add
                    </button>
                    <a data-modal-hide="assign-vehicle-popup"
                        class="assign-vehicle-popup-hide inline-flex items-center px-5 py-2.5 mt-1 text-sm font-medium text-center text-white bg-gray-500 rounded-lg focus:ring-4 focus:ring-blue-200 dark:focus:ring-blue-900 hover:bg-gray-600">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
