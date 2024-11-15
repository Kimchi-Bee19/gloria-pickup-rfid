<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet"/>

    <!-- Prevent FOUC -->
    <meta name="color-scheme" content="light">

    <!--Select2JS-->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!--Custom CSS-->
    {!! $custom_css ?? '' !!}

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

{{--<body class="font-sans antialiased bg-white dark:bg-gray-900 dark:text-white" x-data="{ isOpen: true }">--}}
<body class="font-sans antialiased bg-white dark:bg-gray-900 dark:text-white">
<div class="flex relative">
    <x-navbar>

    </x-navbar>

    <div class = "z-10 fixed top-5 justify-center items-center right-5 bg-white border border-slate-200 rounded-full px-4 py-2 flex gap-3">
        <div class = "border-r border-dark-blue pl-2 pr-5 text-center text-dark-blue">Hello, {{ auth()->user()->name }}</div>
        <form method="POST" action="{{ route('logout') }}" x-data>
            @csrf
            <button
                type="submit"
                class="px-4 py-2 w-full flex items-center rounded-full transition duration-[320] text-dark-blue hover:bg-dark-blue hover:text-slate-100"
            >
                <i class="mr-2 fa-solid fa-sign-out-alt"></i>
                <span class="text-xs leading-none">Log Out</span>
            </button>
        </form>

    </div>
    

{{--    <div :class="isOpen ? 'ml-[180px]' : ''" class="p-10 duration-500 w-[80%]">--}}
    <div class="p-10 duration-500 w-[80%] ml-[180px]">
        <section class="p-3 sm:p-5">
            <div class="mx-auto max-w-screen-xl px-4 lg:px-4">
                {!! $content ?? '' !!}
            </div>
        </section>
    </div>
</div>
<!--Alpine JS-->
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<!--JQuery-->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!--Select2JS-->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
{!! $custom_js ?? '' !!}
</body>
</html>
