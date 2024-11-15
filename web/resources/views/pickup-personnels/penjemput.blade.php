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

                @if ($errors->any())
                    @foreach ($errors->all() as $error)
                        <div class="p-4 mb-6 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400"
                            role="alert">
                            <span class="font-medium">Error!</span> {{ $error }}
                        </div>
                    @endforeach
                @endif
                </div>

                <!-- Title -->
                <h1 class="text-3xl text-dark-blue font-bold mb-5">Daftar Penjemput</h1>

                <!-- Search & Action Buttons -->
                <div class="flex justify-between items-center mb-7">
                    <!-- Search -->
                    <div class="flex-none">
                        <form class="max-w-md" method="get" action="/penjemput/search">
                            <div class="flex items-center">
                                <input type="text" name="search" id="penjemput-search" 
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

                    <!-- Action Buttons (Add Pickup Personnel) -->
                    <div class="flex space-x-3">
                        <!-- Filter -->
                        <div class="relative justify-end">
                            <button id="dropdownClassFilterButton" data-dropdown-toggle="dropdownClassFilter" class="text-gray-900 bg-white border border-gray-300 hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center inline-flex items-center dark:bg-gray-600 dark:text-white dark:border-gray-700 dark:hover:bg-gray-700 dark:focus:ring-blue-800" type="button">
                                {{ $selectedStudent ? $selectedStudent->class . ' - ' . $selectedStudent->full_name : 'Filter Kelas & Nama' }} 
                                <svg class="w-2.5 h-2.5 ms-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4"/>
                                </svg>
                            </button>

                            <!-- Dropdown menu -->
                            <div id="dropdownClassFilter" class="z-10 hidden absolute bg-white divide-y divide-gray-100 rounded-lg shadow w-44 dark:bg-gray-700 overflow-y-auto max-h-40" style="max-height: 10rem;">
                                <div class="p-2">
                                    <input type="text" id="searchFilterInput" placeholder="Cari kelas atau nama" class="w-full px-3 py-2 text-sm border rounded-lg dark:bg-gray-600 dark:text-gray-200" />
                                </div>
                                <ul class="py-2 text-sm text-gray-700 dark:text-gray-200" id="classFilterOptions" aria-labelledby="dropdownClassFilterButton">
                                    <li>
                                        <a href="#" class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white" data-student-id="">Semua Siswa</a>
                                    </li>
                                    @foreach ($students as $student)
                                        <li>
                                            <a href="#" class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white" data-student-id="{{ $student->id }}">{{ $student->class }} - {{ $student->full_name }}</a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>

                        <!-- Add Pickup Personnel Button -->
                        <div class="flex">
                            <a href = '{{ route('pickup-personnel.create') }}'
                                class="flex gap-3 items-center px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class = "h-6 object-contain">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                                <div>Tambah Penjemput</div>
                            </a>
                        </div>

                        <!-- Import Pickup Personnel -->
                        <div class="flex">
                            <a data-modal-target="import-pickup-personnel-popup" data-modal-toggle="import-pickup-personnel-popup"
                                class="cursor-pointer flex gap-3 items-center px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                                <i class="fa-solid fa-file-import"></i>
                                <div>Import Penjemput</div>
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
                                        Siswa
                                        <a href="#">
                                            <svg class="w-3 h-3 ms-1.5" aria-hidden="true" onclick="sortTable(2)" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M8.574 11.024h6.852a2.075 2.075 0 0 0 1.847-1.086 1.9 1.9 0 0 0-.11-1.986L13.736 2.9a2.122 2.122 0 0 0-3.472 0L6.837 7.952a1.9 1.9 0 0 0-.11 1.986 2.074 2.074 0 0 0 1.847 1.086Zm6.852 1.952H8.574a2.072 2.072 0 0 0-1.847 1.087 1.9 1.9 0 0 0 .11 1.985l3.426 5.05a2.123 2.123 0 0 0 3.472 0l3.427-5.05a1.9 1.9 0 0 0 .11-1.985 2.074 2.074 0 0 0-1.846-1.087Z"/>
                                            </svg>
                                        </a>
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    <div class="flex items-center justify-center">
                                        Nama Lengkap
                                        <a href="#">
                                            <svg class="w-3 h-3 ms-1.5" aria-hidden="true" onclick="sortTable(3)" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M8.574 11.024h6.852a2.075 2.075 0 0 0 1.847-1.086 1.9 1.9 0 0 0-.11-1.986L13.736 2.9a2.122 2.122 0 0 0-3.472 0L6.837 7.952a1.9 1.9 0 0 0-.11 1.986 2.074 2.074 0 0 0 1.847 1.086Zm6.852 1.952H8.574a2.072 2.072 0 0 0-1.847 1.087 1.9 1.9 0 0 0 .11 1.985l3.426 5.05a2.123 2.123 0 0 0 3.472 0l3.427-5.05a1.9 1.9 0 0 0 .11-1.985 2.074 2.074 0 0 0-1.846-1.087Z"/>
                                            </svg>
                                        </a>
                                    </div>
                                </th>
                                <!-- <th scope="col" class="px-6 py-3">
                                    <div class="flex items-center justify-center">
                                       Notifikasi
                                        <a href="#">
                                            <svg class="w-3 h-3 ms-1.5" aria-hidden="true" onclick="sortTable(4)" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M8.574 11.024h6.852a2.075 2.075 0 0 0 1.847-1.086 1.9 1.9 0 0 0-.11-1.986L13.736 2.9a2.122 2.122 0 0 0-3.472 0L6.837 7.952a1.9 1.9 0 0 0-.11 1.986 2.074 2.074 0 0 0 1.847 1.086Zm6.852 1.952H8.574a2.072 2.072 0 0 0-1.847 1.087 1.9 1.9 0 0 0 .11 1.985l3.426 5.05a2.123 2.123 0 0 0 3.472 0l3.427-5.05a1.9 1.9 0 0 0 .11-1.985 2.074 2.074 0 0 0-1.846-1.087Z"/>
                                            </svg>
                                        </a>
                                    </div>
                                </th> -->
                                <th scope="col" class="px-6 py-3">
                                    <div class="flex items-center justify-center">
                                        No Telp
                                        <a href="#">
                                            <svg class="w-3 h-3 ms-1.5" aria-hidden="true" onclick="sortTable(5)" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M8.574 11.024h6.852a2.075 2.075 0 0 0 1.847-1.086 1.9 1.9 0 0 0-.11-1.986L13.736 2.9a2.122 2.122 0 0 0-3.472 0L6.837 7.952a1.9 1.9 0 0 0-.11 1.986 2.074 2.074 0 0 0 1.847 1.086Zm6.852 1.952H8.574a2.072 2.072 0 0 0-1.847 1.087 1.9 1.9 0 0 0 .11 1.985l3.426 5.05a2.123 2.123 0 0 0 3.472 0l3.427-5.05a1.9 1.9 0 0 0 .11-1.985 2.074 2.074 0 0 0-1.846-1.087Z"/>
                                            </svg>
                                        </a>
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    <div class="flex items-center justify-center">
                                        Foto
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
                            @forelse($pickup_personnels as $pickup_personnel)
                            <tr class="odd:bg-white odd:dark:bg-gray-900 even:bg-gray-50 even:dark:bg-gray-800 border-b dark:border-gray-700">
                                <th scope="row" class="px-6 py-3 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    {{ $pickup_personnel['id'] }}
                                </th>
                                <td class="px-6 py-3">
                                    <ul>
                                        @if($pickup_personnel->students->isEmpty())
                                            <li>No student assigned yet.</li>
                                        @else
                                            @foreach($pickup_personnel->students as $student)
                                            <li class="flex items-center justify-between py-2">
                                                <!-- Student -->
                                                <span class="pr-2">{{ $student->full_name }}</span>

                                                @php
                                                $student_pickup_personnel_mapping = $pickup_personnel->student_pickup_personnel_mappings
                                                            ->where('student_id', $student->id)
                                                            ->first();
                                                @endphp
                                                <span class="pr-2">{{ $student_pickup_personnel_mapping->relationship_to_student}}</span>
                                                
                                                <!-- Action Icons (Edit and Delete) -->
                                                <div class="flex space-x-1">

                                                    <!-- Edit Icon -->
                                                    <a href="{{ route('pickup-personnel.edit_associate-siswa', [$student->id, $pickup_personnel->id]) }}" class="text-blue-600 hover:text-blue-900">
                                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                                                            <path d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 0 0 0-3.712ZM19.513 8.199l-3.712-3.712-12.15 12.15a5.25 5.25 0 0 0-1.32 2.214l-.8 2.685a.75.75 0 0 0 .933.933l2.685-.8a5.25 5.25 0 0 0 2.214-1.32L19.513 8.2Z" />
                                                        </svg>
                                                    </a>
                                                    
                                                    <!-- Delete Icon -->
                                                    <form action="{{ route('pickup-personnel.delete_associate-siswa', [$student->id, $pickup_personnel->id]) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this tag?');">
                                                        @csrf
                                                        @method('delete')
                                                        <button type="submit" class="text-red-600 hover:text-red-900">
                                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                                                                <path fill-rule="evenodd" d="M16.5 4.478v.227a48.816 48.816 0 0 1 3.878.512.75.75 0 1 1-.256 1.478l-.209-.035-1.005 13.07a3 3 0 0 1-2.991 2.77H8.084a3 3 0 0 1-2.991-2.77L4.087 6.66l-.209.035a.75.75 0 1 1-.256-1.478A48.567 48.567 0 0 1 7.5 4.705v-.227c0-1.564 1.213-2.9 2.816-2.951a52.662 52.662 0 0 1 3.369 0c1.603.051 2.815 1.387 2.815 2.951Zm-6.136-1.452a51.196 51.196 0 0 1 3.273 0C14.39 3.05 15 3.684 15 4.478v.113a49.488 49.488 0 0 0-6 0v-.113c0-.794.609-1.428 1.364-1.452Zm-.355 5.945a.75.75 0 1 0-1.5.058l.347 9a.75.75 0 1 0 1.499-.058l-.346-9Zm5.48.058a.75.75 0 1 0-1.498-.058l-.347 9a.75.75 0 0 0 1.5.058l.345-9Z" clip-rule="evenodd" />
                                                            </svg>
                                                        </button>
                                                    </form>
                                                </div>
                                            </li>
                                            @endforeach
                                        @endif
                                        <!-- Assign Siswa Button -->
                                        <a href="{{ route('pickup-personnel.assign-siswa', [$pickup_personnel->id]) }}" class="flex justify-center items-center px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-opacity-50">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 mr-2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                            </svg>
                                            Add Student
                                        </a>
                                    </ul>
                                </td>
                                <td class="px-6 py-3">
                                    {{ $pickup_personnel['full_name'] }}
                                </td>
                                <!-- <td class="px-6 py-3">
                                    <form action = '{{route('pickup-personnel.toggle-notif', $pickup_personnel->id)}}' method = "post">
                                        @csrf
                                        @method('put')
                                        @if ($pickup_personnel['receive_notifications'])
                                            <button type = "submit" class = "rounded-full px-2 py-1 bg-red-100 text-red-500">
                                                DISABLE
                                            </button>
                                        @else
                                            <button type = "submit" class = "rounded-full px-2 py-1 bg-green-100 text-green-500">
                                                ENABLE
                                            </button>
                                        @endif
                                    </form>
                                </td> -->
                                <td class="px-6 py-3">
                                    {{ $pickup_personnel['phone_number'] }}
                                </td>
                                <td class="px-6 py-3">
                                    <button data-modal-target="image-modal-{{ $pickup_personnel->id }}" data-modal-toggle="image-modal-{{ $pickup_personnel->id }}" class="bg-blue-500 text-white font-medium py-2 px-4 rounded-full hover:bg-blue-600 transition duration-300 ml-2 inline-flex items-center">
                                        View
                                    </button>
                                </td>
                                <td class="px-6 py-3">
                                    {{ $pickup_personnel['notes'] }}
                                </td>
                                <td class="px-6 py-3">
                                    <a href="{{ route('pickup-personnel.edit', $pickup_personnel->id) }}" class="bg-green-500 text-white font-medium py-2 px-4 rounded-full hover:bg-green-600 transition duration-300 inline-flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5 inline-block align-middle mr-1">
                                            <path d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 0 0 0-3.712ZM19.513 8.199l-3.712-3.712-8.4 8.4a5.25 5.25 0 0 0-1.32 2.214l-.8 2.685a.75.75 0 0 0 .933.933l2.685-.8a5.25 5.25 0 0 0 2.214-1.32l8.4-8.4Z" />
                                            <path d="M5.25 5.25a3 3 0 0 0-3 3v10.5a3 3 0 0 0 3 3h10.5a3 3 0 0 0 3-3V13.5a.75.75 0 0 0-1.5 0v5.25a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5V8.25a1.5 1.5 0 0 1 1.5-1.5h5.25a.75.75 0 0 0 0-1.5H5.25Z" />
                                        </svg>
                                        Edit
                                    <a/>
                                    
                                    <button data-modal-target="popup-modal-{{ $pickup_personnel->id }}" data-modal-toggle="popup-modal-{{ $pickup_personnel->id }}" type="button" class="bg-red-500 text-white font-medium py-2 px-4 rounded-full hover:bg-red-600 transition duration-300 ml-2 inline-flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5 inline-block align-middle mr-1">
                                            <path fill-rule="evenodd" d="M16.5 4.478v.227a48.816 48.816 0 0 1 3.878.512.75.75 0 1 1-.256 1.478l-.209-.035-1.005 13.07a3 3 0 0 1-2.991 2.77H8.084a3 3 0 0 1-2.991-2.77L4.087 6.66l-.209.035a.75.75 0 0 1-.256-1.478A48.567 48.567 0 0 1 7.5 4.705v-.227c0-1.564 1.213-2.9 2.816-2.951a52.662 52.662 0 0 1 3.369 0c1.603.051 2.815 1.387 2.815 2.951Zm-6.136-1.452a51.196 51.196 0 0 1 3.273 0C14.39 3.05 15 3.684 15 4.478v.113a49.488 49.488 0 0 0-6 0v-.113c0-.794.609-1.428 1.364-1.452Zm-.355 5.945a.75.75 0 1 0-1.5.058l.347 9a.75.75 0 1 0 1.499-.058l-.346-9Zm5.48.058a.75.75 0 1 0-1.498-.058l-.347 9a.75.75 0 0 0 1.5.058l.345-9Z" clip-rule="evenodd" />
                                        </svg>
                                        Delete
                                    </button>
                                </td>
                            </tr>

                            @include('pickup-personnels.delete')
                            @include('pickup-personnels.view_image')

                            @empty
                                <tr>
                                    <td colspan="9" class="px-6 py-4 text-center text-gray-500">
                                        No pickup personnels are found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class = "mt-10">
                    {{$pickup_personnels->links()}}
                </div>
            </div>
        </div>
        @include('pickup-personnels.import')
    </x-slot>

    <x-slot name="custom_js">
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const dropdownButton = document.getElementById('dropdownClassFilterButton');
                const dropdownMenu = document.getElementById('dropdownClassFilter');
                const classFilterOptions = document.getElementById('classFilterOptions');
                const searchFilterInput = document.getElementById('searchFilterInput');

                // Toggle dropdown
                dropdownButton.addEventListener('click', () => {
                    dropdownMenu.classList.toggle('hidden');
                });

                // Close dropdown when clicking outside
                window.addEventListener('click', (event) => {
                    if (!dropdownButton.contains(event.target) && !dropdownMenu.contains(event.target)) {
                        dropdownMenu.classList.add('hidden');
                    }
                });

                // Update button text and filter students on selection
                classFilterOptions.addEventListener('click', (event) => {
                    if (event.target.tagName === 'A') {
                        const selectedStudent = event.target.textContent;
                        dropdownButton.innerHTML = `
                            ${selectedStudent ? selectedStudent : 'Filter Kelas & Nama'}
                            <svg class="w-2.5 h-2.5 ms-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4"/>
                            </svg>
                        `;
                        filterStudents(event.target.getAttribute('data-student-id'));
                        dropdownMenu.classList.add('hidden');
                    }
                });

                // Search filter
                searchFilterInput.addEventListener('input', function() {
                    const filter = searchFilterInput.value.toLowerCase();
                    const items = classFilterOptions.getElementsByTagName('li');

                    Array.from(items).forEach((item) => {
                        const text = item.innerText.toLowerCase();
                        item.style.display = text.includes(filter) ? '' : 'none';
                    });
                });

                function filterStudents(studentId) {
                    const url = new URL(window.location.href);
                    if (studentId) {
                        url.searchParams.set('student_id', studentId); 
                    } else {
                        url.searchParams.delete('student_id');
                    }
                    window.location.href = url.toString(); 
                }
            });

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