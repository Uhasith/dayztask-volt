<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    @filamentStyles

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])


    @livewireStyles
</head>

<body class="font-sans antialiased" x-data>
    {{-- The navbar with `sticky` and `full-width` --}}
    @livewire('navigation-menu')

    {{-- The main content with `full-width` --}}
    <x-mary-main with-nav full-width>
        {{-- This is a sidebar that works also as a drawer on small screens --}}
        {{-- Notice the `main-drawer` reference here --}}
        <x-slot:sidebar drawer="main-drawer" collapsible class="bg-base-200">
            {{-- Activates the menu item when a route matches the `link` property --}}
            <x-mary-menu activate-by-route>
                <x-mary-menu-item title="Dashboard" icon="o-home" link="{{ route('dashboard') }}" />
                <x-mary-menu-item title="Projects" icon="o-presentation-chart-line"
                    link="{{ route('projects.index') }}" />
                {{-- <x-mary-menu-sub title="Settings" icon="o-cog-6-tooth">
                    <x-mary-menu-item title="Wifi" icon="o-wifi" link="####" />
                    <x-mary-menu-item title="Archives" icon="o-archive-box" link="####" />
                </x-mary-menu-sub> --}}
            </x-mary-menu>
        </x-slot:sidebar>

        {{-- The `$slot` goes here --}}
        <x-slot:content>
            {{ $slot }}
        </x-slot:content>
    </x-mary-main>

    {{--  SEARCH area --}}
    <x-mary-spotlight search-text="Find Projects, Assigned tasks or Users" no-results-text="Ops! Nothing here." />

    {{--  TOAST area --}}
    <x-mary-toast />

    @stack('modals')

    @livewire('notifications')

    @filamentScripts
    @livewireScripts
</body>

</html>