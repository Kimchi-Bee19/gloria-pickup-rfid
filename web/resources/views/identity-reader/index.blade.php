<x-mainlayout>
    <x-slot name="content">
        <div class="flex-initial w-full justify-center items-center">
            <div class="mt-5 duration-500 text-center">
                <div>
                    @include('includes.session-flash')
                </div>
                <h1 class="text-3xl text-dark-blue font-bold mb-5">Identity Readers</h1>

                <h2 class="text-2xl text-dark-blue font-bold mb-5 text-left">Unauthenticated</h2>
                <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                    <table class="w-full text-sm text-center text-gray-500 dark:text-gray-400">
                        <!-- Header -->
                        <thead class="text-xs text-white uppercase bg-dark-blue dark:bg-dark-blue">
                        <tr>
                            @foreach(["client_id", "Firmware Version", "Client Info", "Type", "Actions"] as $header)
                                <th scope="col" class="px-6 py-3">
                                    <div class="flex items-center justify-center">
                                        {{ $header }}
                                    </div>
                                </th>
                            @endforeach
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($setup ?? [] as $item)
                            <tr class="odd:bg-white odd:dark:bg-gray-900 even:bg-gray-50 even:dark:bg-gray-800 border-b dark:border-gray-700">
                                <th
                                    scope="row"
                                    class="px-6 py-3 font-medium text-gray-900 whitespace-nowrap dark:text-white"
                                >
                                    {{ $item['clientId'] }}
                                </th>
                                <td class="px-6 py-3">
                                    {{ $item['firmwareVersion'] }}
                                </td>
                                <td class="px-6 py-3">
                                    {{ $item['clientInfo'] }}
                                </td>
                                <td class="px-6 py-3">
                                    {{ $clientTypeMap[$item['clientType']] }}
                                </td>
                                <td class="px-6 py-3">
                                    <form action="{{route("identity-reader.configure")}}" method="POST">
                                        @csrf
                                        <input type="text" maxlength="255" class="bg-white" name="label"/>
                                        <input type="hidden" name="clientid" value="{{ $item['clientId'] }}">
                                        <input type="hidden" name="username" value="{{ $item['clientId'] }}">
                                        <input
                                            type="hidden"
                                            name="type"
                                            value="{{ $item['clientType'] == 0 ? "student_rfid" : "vehicle_rfid" }}"
                                        >
                                        <button type="submit" class="text-blue-600 hover:text-blue-900">
                                            Configure
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-6 py-4 text-center text-gray-500">
                                    No data found.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <h2 class="text-2xl text-dark-blue font-bold mt-6 mb-5 text-left">Existing</h2>
                <!-- Table -->
                <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                    <table class="w-full text-sm text-center text-gray-500 dark:text-gray-400">
                        <!-- Header -->
                        <thead class="text-xs text-white uppercase bg-dark-blue dark:bg-dark-blue">
                        <tr>
                            @foreach(["ID", "Label", "Username", "Type", "Last Login", "Actions"] as $header)
                                <th scope="col" class="px-6 py-3">
                                    <div class="flex items-center justify-center">
                                        {{ $header }}
                                    </div>
                                </th>
                            @endforeach
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($data as $item)
                            <tr class="odd:bg-white odd:dark:bg-gray-900 even:bg-gray-50 even:dark:bg-gray-800 border-b dark:border-gray-700">
                                <th
                                    scope="row"
                                    class="px-6 py-3 font-medium text-gray-900 whitespace-nowrap dark:text-white"
                                >
                                    {{ $item['id'] }}
                                </th>
                                <td class="px-6 py-3">
                                    {{ $item['label'] }}
                                </td>
                                <td class="px-6 py-3">
                                    {{ $item['username'] }}
                                </td>
                                <td class="px-6 py-3">
                                    {{ $item['type'] }}
                                </td>
                                <td class="px-6 py-3">
                                    {{ $item['last_login'] }}
                                </td>
                                <td class="px-6 py-3">
                                    <form
                                        action="{{ route('identity-reader.delete', $item['id']) }}"
                                        method="POST"
                                        onsubmit="return confirm('Are you sure you want to delete this tag?');"
                                    >
                                        @csrf
                                        @method('delete')
                                        <button type="submit" class="text-red-600 hover:text-red-900">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-6 py-4 text-center text-gray-500">
                                    No data found.
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
                        Showing <span class="font-bold">{{ $data->firstItem() ?? 0 }}</span> to <span class="font-bold">{{ $data->lastItem() ?? 0 }}</span> of <span
                            class="font-bold"
                        >{{ $data->total() }}</span> entries
                    </span>

                    {{ $data->links() }}
                </div>
            </div>
    </x-slot>
</x-mainlayout>
