<x-mainlayout>

    <x-slot name="content">
        <!-- Content -->
        <div class="flex-initial w-full justify-center items-center">
            <div class="mt-5 duration-500 text-center">
                <!-- Notif -->
                <div>
                @if (session('success'))
                    <div class="p-4 mb-6 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400" role="alert">
                        <span class="font-medium">Success!</span> {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="p-4 mb-6 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400" role="alert">
                        <span class="font-medium">Error!</span> {{ session('error') }}
                    </div>
                @endif
                </div>

                <!-- Title -->
                <h1 class="text-3xl text-dark-blue font-bold mb-5">Daftar Tag Kendaraan</h1>

                <!-- Search & Add Vehicle Tag Button -->
                <div class="flex justify-between items-center mb-7">
                    <!-- Search -->
                    <div>
                        <form class="max-w-md" method="get" action="/tag_kendaraan/search">
                            <div class="flex items-center">
                                <input type="text" name="search" id="vehicle-search"
                                    class="block w-full px-4 py-2 text-sm text-gray-900 bg-gray-100 border border-gray-300 rounded-l-full
                                        dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-blue-500"
                                    placeholder="Search..." value="{{ isset($search) ? $search : '' }}">
                                <button type="submit"
                                    class="px-4 py-2 bg-dark-blue text-white rounded-r-full border border-dark-blue hover:bg-blue-600 hover:border-blue-600
                                        focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                                    </svg>
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Add Vehicle Tag Button -->
                    <div class = "flex gap-3">
                    <div>
                        <a href="{{ route('vehicle-identity.create') }}" class="flex items-center px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 mr-2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            Tambah Tag Kendaraan
                        </a>
                    </div>

                    <div>
                        <a data-modal-target="import-vehicle-identity-popup" data-modal-toggle="import-vehicle-identity-popup"
                        class="cursor-pointer flex gap-3 items-center px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                        <i class="fa-solid fa-file-import"></i>
                        <div>Import Tag Kendaraan</div>
                        </a>
                    </div>


                    </div>
                </div>

                <!-- Table -->
                <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                    <table class="w-full text-sm text-center text-gray-500 dark:text-gray-400">
                        <!-- Header -->
                        <thead class="text-xs text-white uppercase bg-dark-blue dark:bg-dark-blue">
                            <tr>
                                <th scope="col" class="px-6 py-3">
                                    <div class="flex items-center justify-center">
                                        ID
                                        <a href="#">
                                            <svg class="w-3 h-3 ms-1.5" aria-hidden="true" onclick="sortTable(0)" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M8.574 11.024h6.852a2.075 2.075 0 0 0 1.847-1.086 1.9 1.9 0 0 0-.11-1.986L13.736 2.9a2.122 2.122 0 0 0-3.472 0L6.837 7.952a1.9 1.9 0 0 0-.11 1.986 2.074 2.074 0 0 0 1.847 1.086Zm6.852 1.952H8.574a2.072 2.072 0 0 0-1.847 1.087 1.9 1.9 0 0 0 .11 1.985l3.426 5.05a2.123 2.123 0 0 0 3.472 0l3.427-5.05a1.9 1.9 0 0 0 .11-1.985 2.074 2.074 0 0 0-1.846-1.087Z"/>
                                            </svg>
                                        </a>
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    <div class="flex items-center justify-center">
                                        Type
                                        <a href="#">
                                            <svg class="w-3 h-3 ms-1.5" aria-hidden="true" onclick="sortTable(1)" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M8.574 11.024h6.852a2.075 2.075 0 0 0 1.847-1.086 1.9 1.9 0 0 0-.11-1.986L13.736 2.9a2.122 2.122 0 0 0-3.472 0L6.837 7.952a1.9 1.9 0 0 0-.11 1.986 2.074 2.074 0 0 0 1.847 1.086Zm6.852 1.952H8.574a2.072 2.072 0 0 0-1.847 1.087 1.9 1.9 0 0 0 .11 1.985l3.426 5.05a2.123 2.123 0 0 0 3.472 0l3.427-5.05a1.9 1.9 0 0 0 .11-1.985 2.074 2.074 0 0 0-1.846-1.087Z"/>
                                            </svg>
                                        </a>
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    <div class="flex items-center justify-center">
                                        Tag ID (HEX)
                                        <a href="#">
                                            <svg class="w-3 h-3 ms-1.5" aria-hidden="true" onclick="sortTable(2)" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M8.574 11.024h6.852a2.075 2.075 0 0 0 1.847-1.086 1.9 1.9 0 0 0-.11-1.986L13.736 2.9a2.122 2.122 0 0 0-3.472 0L6.837 7.952a1.9 1.9 0 0 0-.11 1.986 2.074 2.074 0 0 0 1.847 1.086Zm6.852 1.952H8.574a2.072 2.072 0 0 0-1.847 1.087 1.9 1.9 0 0 0 .11 1.985l3.426 5.05a2.123 2.123 0 0 0 3.472 0l3.427-5.05a1.9 1.9 0 0 0 .11-1.985 2.074 2.074 0 0 0-1.846-1.087Z"/>
                                            </svg>
                                        </a>
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    <div class="flex items-center justify-center">
                                        EPC (HEX)
                                        <a href="#">
                                            <svg class="w-3 h-3 ms-1.5" aria-hidden="true" onclick="sortTable(2)" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M8.574 11.024h6.852a2.075 2.075 0 0 0 1.847-1.086 1.9 1.9 0 0 0-.11-1.986L13.736 2.9a2.122 2.122 0 0 0-3.472 0L6.837 7.952a1.9 1.9 0 0 0-.11 1.986 2.074 2.074 0 0 0 1.847 1.086Zm6.852 1.952H8.574a2.072 2.072 0 0 0-1.847 1.087 1.9 1.9 0 0 0 .11 1.985l3.426 5.05a2.123 2.123 0 0 0 3.472 0l3.427-5.05a1.9 1.9 0 0 0 .11-1.985 2.074 2.074 0 0 0-1.846-1.087Z"/>
                                            </svg>
                                        </a>
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    <div class="flex items-center justify-center">
                                        Notes
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    <div class="flex items-center justify-center">
                                        Action
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- @php
                                $no = ($vehicle_identities->currentPage() - 1) * $vehicle_identities->perPage() + 1;
                            @endphp -->
                            @forelse($vehicle_identities as $vehicle_identity)
                            <tr class="odd:bg-white odd:dark:bg-gray-900 even:bg-gray-50 even:dark:bg-gray-800 border-b dark:border-gray-700">
                                <th scope="row" class="px-6 py-3 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    {{ $vehicle_identity['id'] }}
                                </th>
                                <td class="px-6 py-3">
                                    {{ $vehicle_identity['type'] }}
                                </td>
                                <td class="px-6 py-3">
                                    {{ $vehicle_identity['tag_id'] }}
                                </td>
                                <td class="px-6 py-3">
                                    {{ $vehicle_identity['auth_check'] }}
                                </td>
                                <td class="px-6 py-3">
                                    {{ $vehicle_identity['notes'] }}
                                </td>
                                <td class="px-6 py-3">
                                    <a href="{{ route('vehicle-identity.edit', $vehicle_identity->id) }}" class="bg-green-500 text-white font-medium py-2 px-4 rounded-full hover:bg-green-600 transition duration-300 inline-flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5 inline-block align-middle mr-1">
                                            <path d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 0 0 0-3.712ZM19.513 8.199l-3.712-3.712-8.4 8.4a5.25 5.25 0 0 0-1.32 2.214l-.8 2.685a.75.75 0 0 0 .933.933l2.685-.8a5.25 5.25 0 0 0 2.214-1.32l8.4-8.4Z" />
                                            <path d="M5.25 5.25a3 3 0 0 0-3 3v10.5a3 3 0 0 0 3 3h10.5a3 3 0 0 0 3-3V13.5a.75.75 0 0 0-1.5 0v5.25a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5V8.25a1.5 1.5 0 0 1 1.5-1.5h5.25a.75.75 0 0 0 0-1.5H5.25Z" />
                                        </svg>
                                        Edit
                                    </a>

                                    <button data-modal-target="popup-modal-{{ $vehicle_identity->id }}" data-modal-toggle="popup-modal-{{ $vehicle_identity->id }}" type="button" class="bg-red-500 text-white font-medium py-2 px-4 rounded-full hover:bg-red-600 transition duration-300 ml-2 inline-flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5 inline-block align-middle mr-1">
                                            <path fill-rule="evenodd" d="M16.5 4.478v.227a48.816 48.816 0 0 1 3.878.512.75.75 0 1 1-.256 1.478l-.209-.035-1.005 13.07a3 3 0 0 1-2.991 2.77H8.084a3 3 0 0 1-2.991-2.77L4.087 6.66l-.209.035a.75.75 0 0 1-.256-1.478A48.567 48.567 0 0 1 7.5 4.705v-.227c0-1.564 1.213-2.9 2.816-2.951a52.662 52.662 0 0 1 3.369 0c1.603.051 2.815 1.387 2.815 2.951Zm-6.136-1.452a51.196 51.196 0 0 1 3.273 0C14.39 3.05 15 3.684 15 4.478v.113a49.488 49.488 0 0 0-6 0v-.113c0-.794.609-1.428 1.364-1.452Zm-.355 5.945a.75.75 0 1 0-1.5.058l.347 9a.75.75 0 1 0 1.499-.058l-.346-9Zm5.48.058a.75.75 0 1 0-1.498-.058l-.347 9a.75.75 0 0 0 1.5.058l.345-9Z" clip-rule="evenodd" />
                                        </svg>
                                        Delete
                                    </button>
                                </td>
                            </tr>

                            @include('vehicle-identities.delete')

                            @empty
                                <tr>
                                    <td colspan="9" class="px-6 py-4 text-center text-gray-500">
                                        No vehicle tags are found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Label & Dropdown -->
                {{-- <div class="flex items-center justify-between mb-5 mt-3">
                    <!-- Showing Text -->
                    <span class="text-sm">
                        Showing <span class="font-bold">{{ $vehicle_identities->firstItem() }}</span> to <span class="font-bold">{{ $vehicle_identities->lastItem() }}</span> of <span class="font-bold">{{ $vehicle_identities->total() }}</span> entries
                    </span>

                    <!-- Dropdown -->
                    <div class="flex items-center">
                        <form method="get" action="{{ url('/tag_kendaraan') }}" id="perPageForm">
                            <label for="perPage" class="mr-2 text-sm">Show</label>
                            <select name="perPage" id="perPage" class="rounded-md text-sm w-20 h-9 border border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200" onchange="document.getElementById('perPageForm').submit();">
                                <!-- <option value="2" {{ $perPage == 2 ? 'selected' : '' }}>2</option> -->
                                <option value="5" {{ $perPage == 5 ? 'selected' : '' }}>5</option>
                                <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                                <option value="20" {{ $perPage == 20 ? 'selected' : '' }}>20</option>
                            </select>
                            <span class="ml-2 text-sm">entries</span>
                        </form>
                    </div>
                </div> --}}

                <!-- Pagination -->
                {{-- <div class="flex justify-center mt-7">
                    <nav aria-label="Page navigation example">
                        <ul class="flex items-center -space-x-px h-10 text-base">
                            <!-- Tombol Previous -->
                            <li>
                                <a href="{{ $vehicle_identities->previousPageUrl() }}" class="flex items-center justify-center px-4 h-10 ms-0 leading-tight text-gray-500 bg-white border border-e-0 border-gray-300 rounded-s-lg hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white {{ $vehicle_identities->onFirstPage() ? 'cursor-not-allowed opacity-50' : '' }}" {{ $vehicle_identities->onFirstPage() ? 'aria-disabled=true' : '' }}>
                                    <span class="sr-only">Previous</span>
                                    <svg class="w-3 h-3 rtl:rotate-180" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 1 1 5l4 4"/>
                                    </svg>
                                </a>
                            </li>

                            <!-- Links Halaman -->
                            @foreach(range(1, $vehicle_identities->lastPage()) as $i)
                                <li>
                                    <a href="{{ $vehicle_identities->url($i) }}" class="flex items-center justify-center px-4 h-10 leading-tight {{ $vehicle_identities->currentPage() == $i ? 'text-blue-600 border border-blue-300 bg-blue-50' : 'text-gray-500 bg-white border border-gray-300' }} hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">
                                        {{ $i }}
                                    </a>
                                </li>
                            @endforeach

                            <!-- Tombol Next -->
                            <li>
                                <a href="{{ $vehicle_identities->nextPageUrl() }}" class="flex items-center justify-center px-4 h-10 leading-tight text-gray-500 bg-white border border-gray-300 rounded-e-lg hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white {{ !$vehicle_identities->hasMorePages() ? 'cursor-not-allowed opacity-50' : '' }}" {{ !$vehicle_identities->hasMorePages() ? 'aria-disabled=true' : '' }}>
                                    <span class="sr-only">Next</span>
                                    <svg class="w-3 h-3 rtl:rotate-180" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
                                    </svg>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div> --}}
                <div class = "mt-10">
                    {{$vehicle_identities->links()}}
                </div>
                
            </div>
        </div>

        @include('vehicle-identities.import')
    </x-slot>

    <x-slot name="custom_js">
        <script>
            function sortTable(columnIndex) {
                const table = document.querySelector('table');
                const rows = Array.from(table.querySelectorAll('tbody tr'));
                const header = table.querySelectorAll('th')[columnIndex];
                const isAsc = header.classList.contains('asc');

                rows.sort((a, b) => {
                    let aText = a.cells[columnIndex].innerText.trim();
                    let bText = b.cells[columnIndex].innerText.trim();

                    const aValue = isNaN(aText) ? aText.toLowerCase() : parseFloat(aText);
                    const bValue = isNaN(bText) ? bText.toLowerCase() : parseFloat(bText);

                    return isAsc ? (aValue > bValue ? 1 : -1) : (aValue < bValue ? 1 : -1);
                });

                const tbody = table.querySelector('tbody');
                tbody.innerHTML = '';
                rows.forEach(row => tbody.appendChild(row));

                table.querySelectorAll('th').forEach(th => th.classList.remove('asc', 'desc'));
                header.classList.toggle('asc', !isAsc);
                header.classList.toggle('desc', isAsc);
            }
        </script>
    </x-slot>

</x-mainlayout>
