<div
    id="import-pickup-personnel-popup" tabindex="-1"
    class="hidden fixed top-0 left-0 right-0 bottom-0 z-50 flex items-center justify-center w-full p-4 overflow-x-hidden overflow-y-auto h-full bg-black bg-opacity-50"
>
    <!-- Form -->
    <div class="bg-white w-full max-w-md max-h-full relative rounded-lg shadow dark:bg-gray-700">
        <div class="px-4 mx-auto my-auto max-w-2xl lg:py-8">
            <!-- Title -->
            <h1 class="title text-3xl text-dark-blue font-bold mb-5 text-center">
               Import Penjemput</h1>
            <form
                class="import-pickup-personnel"
                action="{{route('pickup-personnel.import')}}"
                enctype="multipart/form-data"
                method="POST"
            >
                @csrf
                <div class="grid gap-4 sm:grid-cols-2 sm:gap-6">
                    <div class="sm:col-span-2">
                        <div class = "font-bold">Upload file csv, xls, atau xlsx dengan ketentuan sebagai berikut </div>
                        Kolom yang wajib ada: 
                        <ul class = "list-disc list-inside">
                            <li>full_name (nama lengkap penjemput)</li>
                        </ul>
                        Kolom yang boleh ada, namun tidak wajib:
                        <ul class = "list-disc list-inside">
                            <li>phone_number (no telp penjemput)</li>
                        </ul>
                        Jika ingin mengasosiasikan siswa:
                        <ul class = "list-disc list-inside">
                            <li>Untuk siswa pertama, buat kolom internal_id1, full_name1, call_name1, class1</li>
                            <li>Untuk siswa kedua, buat kolom internal_id2, full_name2, call_name2, class2</li>
                            <li>Dan seterusnya...</li>
                        </ul>
                    </div>
                        <label
                            for="file"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"
                        >Upload file</label>
                        <input
                            type="file" name="file"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                        >
                    </div>
                    <div class="flex justify-center space-x-4 mt-8">
                        <button
                            type="submit"
                            class="cursor-pointer inline-flex items-center px-5 py-2.5 mt-1 text-sm font-medium text-center text-white bg-blue-500 rounded-lg focus:ring-4 focus:ring-blue-200 dark:focus:ring-blue-900 hover:bg-blue-600"
                        >
                            Import
                        </button>
                        <a
                            data-modal-hide="import-pickup-personnel-popup"
                            class="cursor-pointer inline-flex items-center px-5 py-2.5 mt-1 text-sm font-medium text-center text-white bg-gray-500 rounded-lg focus:ring-4 focus:ring-blue-200 dark:focus:ring-blue-900 hover:bg-gray-600"
                        >
                            Cancel
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

