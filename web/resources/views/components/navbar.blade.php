@php
    $menus = [
        [
            'title' => 'Profile',
            'icon' => "<i class = 'mr-2 fa-solid fa-user'></i>",
            'route' => 'profile.edit'
        ],
        [
            'title' => 'Manage Admin',
            'icon' => "<i class = 'mr-2 fas fa-user-cog'></i>",
            'route' => 'admin.index',
        ],
        [
            'title' => 'Live Admin',
            'icon' => "<i class='mr-2 fa-solid fa-user-pen'></i>",
            'route' => 'live-admin.index',
        ],
        [
            'title' => 'Devices',
            'submenus' => [
                ['title' => 'Live Display',  'icon'=> "<i class = 'mr-2 fa-solid fa-tv'></i>", 'route' => 'live-display.index'],
                ['title' => 'Identity Reader',  'icon'=> "<i class='mr-2 fa-brands fa-nfc-symbol'></i>", 'route' => 'identity-reader.index'],
            ],
        ],
        [
            'title' => 'Siswa',
            'icon' => 'fa-solid fa-users',
            'submenus' => [
                ['title' => 'Tag Siswa','icon'=> "<i class = 'mr-2 fa-solid fa-tag'></i>", 'route' => 'tag_siswa'],
                ['title' => 'Siswa', 'icon'=> "<i class = 'mr-2 fa-solid fa-child'></i>",'route' => 'siswa'],
            ],
        ],
        [
            'title' => 'Kendaraan',
            'submenus' => [
                ['title' => 'Tag Kendaraan', 'icon'=> "<i class = 'mr-2 fa-solid fa-tag'></i>", 'route' => 'vehicle-identity.index'],
                ['title' => 'Kendaraan', 'icon'=> "<i class = 'mr-2 fa-solid fa-car'></i>", 'route' => 'vehicle.index'],
            ],
        ],
        [
            'title' => 'Penjemput',
            'icon' => 'fa-solid fa-users',
            'submenus' => [
                ['title' => 'Penjemput', 'icon'=> "<i class = 'mr-2 fa-solid fa-person'></i>",'route' => 'pickup-personnel.index'],
            ],
        ],
        [
            'title' => 'History',
            'icon' => 'fa-solid fa-clock-rotate-left',
            'submenus' => [
                ['title' => 'Arrival Log',  'icon'=> "<i class = 'mr-2 fa-solid fa-arrow-down'></i>", 'route' => 'arrival-log.index'],
                ['title' => 'Departure Log',  'icon'=> "<i class = 'mr-2 fa-solid fa-arrow-up'></i>", 'route' => 'departure-log.index'],
                ['title' => 'Arrival Departure',  'icon'=> "<i class = 'mr-2 fa fa-map-marker-alt'></i>", 'route' => 'tracking.index']
            ],
        ],
        
    ];
@endphp

<nav x-data="{ isOpen: true }" class="">
    <div
        id="side-nav"
        class="w-[200px] hidden sm:flex sm:w-62 fixed h-screen flex-col justify-between top-0 rounded-tr-3xl overflow-hidden"
    >
        <!-- Logo Section -->
        <img
            id="logo-gloria" class="duration-300 h-[15%] px-5 py-3 object-contain"
            :src="isOpen ? 'https://gloriaschool.org/wp-content/uploads/2020/06/logo-png-trans.png' :
                '{{ asset('images/logo-kecil.jpeg') }}'"
        />

        <!-- Sidebar Content -->
        <div
            :class="{ 'w-[200px]': isOpen, 'w-[75px]': !isOpen }"
            class="duration-300 overflow-y-auto rounded-tr-3xl bg-gradient-to-top no-scrollbar relative h-[85%] flex flex-col justify-between pt-5 pb-10 pl-5 pr-5"
        >
            <!-- Toggle Button -->
            {{-- <div
            @click="isOpen = !isOpen"
            id="collapse-nav"
                :class="{ '': isOpen, 'rotate-180': !isOpen }"
                class="duration-500 cursor-pointer w-[30px] h-[30px] absolute right-[-10px] top-10 bg-white text-dark-blue rounded-full border border-dark-blue flex justify-center items-center">
                <i class="text-xs fa-solid fa-arrow-left"></i>
            </div> --}}

            <!-- Navigation Menu -->
            <div id="nav-list" class="flex flex-col">
                @foreach ($menus as $menu)
                    {{-- <div class="text-slate-100 p-2 mb-2"> <!-- Tambah padding di sini --> --}}
                    @if(isset($menu['route']))
                    
                        <x-navlink :active="request()->routeIs($menu['route'])" href="{{route($menu['route']) }}">
                            <x-slot name='icon'>
                                {!! isset($menu['icon']) ? $menu['icon'] : '' !!}
                            </x-slot>
                            <x-slot name='title'>
                                {{ $menu['title'] }}
                            </x-slot>
                        </x-navlink>
                    @else
                        <div
                            {{ $attributes->merge(['class' => 'border-b border-white h-[30px] text-xs mb-2 flex items-center text-white']) }}
                            :class="{ 'pr-2': !isOpen, 'px-4': isOpen}"
                        >
                            {{-- Title --}}
                            <div :class="{ 'hidden': !isOpen, '': isOpen }">
                                {{ $menu['title'] }}
                            </div>
                        </div>
                    @endif

                    @if(array_key_exists('submenus', $menu))
                        @foreach ($menu['submenus'] as $submenu)
                            <x-navlink
                                :active="request()->routeIs($submenu['route'])"
                                href="{{ route($submenu['route']) }}"
                            >
                                <x-slot name='icon'>
                                    {!! isset($submenu['icon']) ? $submenu['icon'] : ''  !!}
                                </x-slot>

                                <x-slot name='title'>
                                    {{ $submenu['title'] }}
                                </x-slot>
                            </x-navlink>
                        @endforeach
                    @endif
                    {{-- </div> --}}
                @endforeach

                <!-- Form Logout -->
                 <!-- Tambah padding di sini -->
                    
                
            </div>
        </div>
    </div>
</nav>


<!--Mobile View-->
{{-- <div class = "fixed sm:hidden">
    <div @click = "isOpen= !isOpen" class = "h-[56px] flex items-center bg-gradient-to-top px-5">
        <i class="text-slate-100 cursor-pointer fa-solid fa-bars"></i>
    </div>
    <div @click = "isOpen= !isOpen" :class="{ 'hidden': !isOpen, '': isOpen }"
        class = "fixed top-0 left-0 h-screen w-screen bg-black opacity-50"></div>
    <div :class="{ 'w-0': !isOpen, 'w-[220px] p-5': isOpen }"
        class = "duration-300 absolute left-0 top-0 h-screen bg-gradient-to-top flex flex-col gap-4 rounded-tr-3xl">
        <img id = "logo-gloria"
            class = "duration-500 px-1 py-2 h-[20%] object-contain flex items-center justify-center"
            src="https://gloriaschool.org/wp-content/uploads/2020/06/logo-png-trans.png">
        </img>
        <div :class="{ 'hidden': !isOpen, 'w-[170px]': isOpen }" class = "duration-500 flex flex-col gap-2">
            <a href = ""
                class = "p-2 px-4 rounded-full text-slate-100 hover:text-dark-blue hover:bg-slate-100 transition-all ease-in-out duration-600 flex items-center gap-3">
                <i class="text-xs fa-solid fa-house"></i>
                <div class = "ml-2 leading-none font-semibold text-xs">Home</div>
            </a>
            <a href = ""
                class = "px-4 p-2 rounded-full text-slate-100 hover:text-dark-blue hover:bg-slate-100 transition-all ease-in-out duration-600 flex items-center gap-3">
                <i class="text-xs fa-solid fa-car"></i>
                <div class = "ml-2 leading-none font-semibold text-xs">
                    List Kendaraan</div>
            </a>
            <a href = ""
                class = "px-4 p-2 rounded-full text-slate-100 hover:text-dark-blue hover:bg-slate-100 transition-all ease-in-out duration-600 flex items-center gap-3">
                <i class="text-xs fa-solid fa-users"></i>
                <div class = "ml-2 leading-none font-semibold text-xs">
                    List Siswa</div>
            </a>
            <a href = ""
                class = "px-4 p-2 rounded-full text-slate-100 hover:text-dark-blue hover:bg-slate-100 transition-all ease-in-out duration-600 flex items-center gap-3">
                <i class="text-xs fa-solid fa-eye"></i>
                <div class = "ml-2 leading-none font-semibold text-xs">
                    Live View</div>
            </a>
            <a href = ""
                class = "px-4 p-2 rounded-full text-slate-100 hover:text-dark-blue hover:bg-slate-100 transition-all ease-in-out duration-600 flex items-center gap-3">
                <i class="text-xs fa-solid fa-clock"></i>
                <div class = "ml-2 leading-none font-semibold text-xs">
                    Ubah Jam</div>
            </a>
            <a href = ""
                class = "px-4 p-2 rounded-full text-slate-100 hover:text-dark-blue hover:bg-slate-100 transition-all ease-in-out duration-600 flex items-center gap-3">
                <i class="text-xs fa-solid fa-clock-rotate-left"></i>
                <div class = "ml-2 leading-none font-semibold text-xs">
                    Histori</div>
            </a>
        </div>
    </div>
</div> --}}

