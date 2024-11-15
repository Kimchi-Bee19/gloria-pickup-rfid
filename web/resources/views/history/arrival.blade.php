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
                <h1 class="text-3xl text-dark-blue font-bold mb-5">Daftar Arrival Log</h1>

                <!-- Search & Add Vehicle Tag Button -->
                <div class="flex justify-between items-center mb-7">
                    <!-- Search -->
                    <div>
                        <form class="max-w-md" method="get" action="/arrival-log/search">
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
                                        RFID Tag
                                        <a href="#">
                                            <svg class="w-3 h-3 ms-1.5" aria-hidden="true" onclick="sortTable(1)" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M8.574 11.024h6.852a2.075 2.075 0 0 0 1.847-1.086 1.9 1.9 0 0 0-.11-1.986L13.736 2.9a2.122 2.122 0 0 0-3.472 0L6.837 7.952a1.9 1.9 0 0 0-.11 1.986 2.074 2.074 0 0 0 1.847 1.086Zm6.852 1.952H8.574a2.072 2.072 0 0 0-1.847 1.087 1.9 1.9 0 0 0 .11 1.985l3.426 5.05a2.123 2.123 0 0 0 3.472 0l3.427-5.05a1.9 1.9 0 0 0 .11-1.985 2.074 2.074 0 0 0-1.846-1.087Z"/>
                                            </svg>
                                        </a>
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    <div class="flex items-center justify-center">
                                        Type
                                        <a href="#">
                                            <svg class="w-3 h-3 ms-1.5" aria-hidden="true" onclick="sortTable(2)" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M8.574 11.024h6.852a2.075 2.075 0 0 0 1.847-1.086 1.9 1.9 0 0 0-.11-1.986L13.736 2.9a2.122 2.122 0 0 0-3.472 0L6.837 7.952a1.9 1.9 0 0 0-.11 1.986 2.074 2.074 0 0 0 1.847 1.086Zm6.852 1.952H8.574a2.072 2.072 0 0 0-1.847 1.087 1.9 1.9 0 0 0 .11 1.985l3.426 5.05a2.123 2.123 0 0 0 3.472 0l3.427-5.05a1.9 1.9 0 0 0 .11-1.985 2.074 2.074 0 0 0-1.846-1.087Z"/>
                                            </svg>
                                        </a>
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    <div class="flex items-center justify-center">
                                        Model
                                        <a href="#">
                                            <svg class="w-3 h-3 ms-1.5" aria-hidden="true" onclick="sortTable(3)" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M8.574 11.024h6.852a2.075 2.075 0 0 0 1.847-1.086 1.9 1.9 0 0 0-.11-1.986L13.736 2.9a2.122 2.122 0 0 0-3.472 0L6.837 7.952a1.9 1.9 0 0 0-.11 1.986 2.074 2.074 0 0 0 1.847 1.086Zm6.852 1.952H8.574a2.072 2.072 0 0 0-1.847 1.087 1.9 1.9 0 0 0 .11 1.985l3.426 5.05a2.123 2.123 0 0 0 3.472 0l3.427-5.05a1.9 1.9 0 0 0 .11-1.985 2.074 2.074 0 0 0-1.846-1.087Z"/>
                                            </svg>
                                        </a>
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    <div class="flex items-center justify-center">
                                        Warna
                                        <a href="#">
                                            <svg class="w-3 h-3 ms-1.5" aria-hidden="true" onclick="sortTable(4)" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M8.574 11.024h6.852a2.075 2.075 0 0 0 1.847-1.086 1.9 1.9 0 0 0-.11-1.986L13.736 2.9a2.122 2.122 0 0 0-3.472 0L6.837 7.952a1.9 1.9 0 0 0-.11 1.986 2.074 2.074 0 0 0 1.847 1.086Zm6.852 1.952H8.574a2.072 2.072 0 0 0-1.847 1.087 1.9 1.9 0 0 0 .11 1.985l3.426 5.05a2.123 2.123 0 0 0 3.472 0l3.427-5.05a1.9 1.9 0 0 0 .11-1.985 2.074 2.074 0 0 0-1.846-1.087Z"/>
                                            </svg>
                                        </a>
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    <div class="flex items-center justify-center">
                                        Plat Nomor
                                        <a href="#">
                                            <svg class="w-3 h-3 ms-1.5" aria-hidden="true" onclick="sortTable(5)" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M8.574 11.024h6.852a2.075 2.075 0 0 0 1.847-1.086 1.9 1.9 0 0 0-.11-1.986L13.736 2.9a2.122 2.122 0 0 0-3.472 0L6.837 7.952a1.9 1.9 0 0 0-.11 1.986 2.074 2.074 0 0 0 1.847 1.086Zm6.852 1.952H8.574a2.072 2.072 0 0 0-1.847 1.087 1.9 1.9 0 0 0 .11 1.985l3.426 5.05a2.123 2.123 0 0 0 3.472 0l3.427-5.05a1.9 1.9 0 0 0 .11-1.985 2.074 2.074 0 0 0-1.846-1.087Z"/>
                                            </svg>
                                        </a>
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    <div class="flex items-center justify-center">
                                       Timestamp
                                       <a href="#">
                                            <svg class="w-3 h-3 ms-1.5" aria-hidden="true" onclick="sortTable(6)" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M8.574 11.024h6.852a2.075 2.075 0 0 0 1.847-1.086 1.9 1.9 0 0 0-.11-1.986L13.736 2.9a2.122 2.122 0 0 0-3.472 0L6.837 7.952a1.9 1.9 0 0 0-.11 1.986 2.074 2.074 0 0 0 1.847 1.086Zm6.852 1.952H8.574a2.072 2.072 0 0 0-1.847 1.087 1.9 1.9 0 0 0 .11 1.985l3.426 5.05a2.123 2.123 0 0 0 3.472 0l3.427-5.05a1.9 1.9 0 0 0 .11-1.985 2.074 2.074 0 0 0-1.846-1.087Z"/>
                                            </svg>
                                        </a>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- @php
                                $no = ($arrivals->currentPage() - 1) * $arrivals->perPage() + 1;
                            @endphp -->
                            @forelse($arrivals as $arrival)
                            <tr class="odd:bg-white odd:dark:bg-gray-900 even:bg-gray-50 even:dark:bg-gray-800 border-b dark:border-gray-700">
                                <th scope="row" class="px-6 py-3 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    {{ $arrival['id'] }}
                                </th>
                                <td class="px-6 py-3">
                                    {{ $arrival->vehicle_identity->tag_id ?: "" }}
                                </td>
                                <td class="px-6 py-3">
                                    {{ $arrival->vehicle->type }}
                                </td>
                                <td class="px-6 py-3">
                                    @if ($arrival->vehicle->model == null)
                                        -
                                    @else
                                        {{ $arrival->vehicle->model }}
                                    @endif
                                </td>
                                <td class="px-6 py-3">
                                    {{ $arrival->vehicle->color }}
                                </td>
                                <td class="px-6 py-3">
                                    {{ $arrival->vehicle->license_plate }}
                                </td>
                                <td class="px-6 py-3">
                                    {{ date(' H:i:s d-M-Y ', strtotime($arrival->arrival_time)) }}
                                </td>
                            </tr>

                            @empty
                                <tr>
                                    <td colspan="9" class="px-6 py-4 text-center text-gray-500">
                                        No arrival logs are found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Label & Dropdown -->
                <div class="flex items-center justify-between mb-5 mt-3">
                    <!-- Showing Text -->
                    <span class="text-sm">
                        Showing <span class="font-bold">{{ $arrivals->firstItem() }}</span> to <span class="font-bold">{{ $arrivals->lastItem() }}</span> of <span class="font-bold">{{ $arrivals->total() }}</span> entries
                    </span>

                    <!-- Dropdown -->
                    <div class="flex items-center">
                        <form method="get" action="{{ url('/admin') }}" id="perPageForm">
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
                </div>

                <!-- Pagination -->
                <div class="flex justify-center mt-7">
                    <nav aria-label="Page navigation example">
                        <ul class="flex items-center -space-x-px h-10 text-base">
                            <!-- Tombol Previous -->
                            <li>
                                <a href="{{ $arrivals->previousPageUrl() }}" class="flex items-center justify-center px-4 h-10 ms-0 leading-tight text-gray-500 bg-white border border-e-0 border-gray-300 rounded-s-lg hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white {{ $arrivals->onFirstPage() ? 'cursor-not-allowed opacity-50' : '' }}" {{ $arrivals->onFirstPage() ? 'aria-disabled=true' : '' }}>
                                    <span class="sr-only">Previous</span>
                                    <svg class="w-3 h-3 rtl:rotate-180" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 1 1 5l4 4"/>
                                    </svg>
                                </a>
                            </li>

                            <!-- Links Halaman -->
                            @foreach(range(1, $arrivals->lastPage()) as $i)
                                <li>
                                    <a href="{{ $arrivals->url($i) }}" class="flex items-center justify-center px-4 h-10 leading-tight {{ $arrivals->currentPage() == $i ? 'text-blue-600 border border-blue-300 bg-blue-50' : 'text-gray-500 bg-white border border-gray-300' }} hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">
                                        {{ $i }}
                                    </a>
                                </li>
                            @endforeach

                            <!-- Tombol Next -->
                            <li>
                                <a href="{{ $arrivals->nextPageUrl() }}" class="flex items-center justify-center px-4 h-10 leading-tight text-gray-500 bg-white border border-gray-300 rounded-e-lg hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white {{ !$arrivals->hasMorePages() ? 'cursor-not-allowed opacity-50' : '' }}" {{ !$arrivals->hasMorePages() ? 'aria-disabled=true' : '' }}>
                                    <span class="sr-only">Next</span>
                                    <svg class="w-3 h-3 rtl:rotate-180" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
                                    </svg>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
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
