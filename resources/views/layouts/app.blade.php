<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'DayzTask') }}</title>

    <!-- Favicons -->
    <link rel="icon" type="image/png" href="/laravel.248x256.png">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Quill stylesheet -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">

    <!-- Filepond stylesheet -->
    <link href="https://unpkg.com/filepond@^4/dist/filepond.css" rel="stylesheet" />
    <link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css" rel="stylesheet" />

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    @filamentStyles
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles
    @wireUiScripts
    @stack('styles')
</head>

<body class="font-sans antialiased" x-data>
    {{-- The navbar with `sticky` and `full-width` --}}
    @livewire('navigation-menu')

    {{-- The main content with `full-width` --}}
    <x-mary-main with-nav full-width>
        {{-- This is a sidebar --}}
        <x-slot:sidebar drawer="main-drawer" collapsible
            class="bg-base-200 dark:bg-gray-800 border-r border-gray-100 dark:border-gray-700">
            <x-mary-menu activate-by-route>
                <x-mary-menu-item title="Dashboard" icon="o-home" link="{{ route('dashboard') }}" wire:navigate />
                <x-mary-menu-item title="Projects" icon="o-presentation-chart-line"
                    link="{{ route('projects.index') }}" wire:navigate/>
                @if (auth()->user()->hasTeamRole(auth()->user()->currentTeam, 'admin'))
                    <x-mary-menu-item title="Summary" icon="o-chart-bar" link="{{ route('summary.index') }}" wire:navigate/>
                    <livewire:pages.checklist.components.checklist-sidebar-icon />
                    <x-mary-menu-item title="Leave Approvals" icon="o-calendar-date-range" link="{{ route('event-approvals') }}" wire:navigate/>
                @endif
                <x-mary-menu-item title="Messenger" icon="o-chat-bubble-left-right" link="{{ route('messenger') }}" wire:navigate/>

                {{-- <x-mary-menu-sub title="Settings" icon="o-cog-6-tooth">
                    <x-mary-menu-item title="Wifi" icon="o-wifi" link="####" />
                    <x-mary-menu-item title="Archives" icon="o-archive-box" link="####" />
                </x-mary-menu-sub> --}}
            </x-mary-menu>
        </x-slot:sidebar>

        <x-slot:content class="!p-0">
            {{ $slot }}
        </x-slot:content>
    </x-mary-main>

    <x-wui-dialog />

    @stack('modals')

    @livewire('notifications')

    <script>
        window.Laravel = {!! json_encode([
            'csrfToken' => csrf_token(),
            'userId' => auth()->check() ? auth()->user()->id : null,
        ]) !!};
    </script>

    @filamentScripts
    @livewireScripts
    @stack('scripts')
    <script src="https://unpkg.com/@victoryoalli/alpinejs-timeout@1.0.0/dist/timeout.min.js" defer></script>

    <!-- Include the Quill library -->
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>

    <!-- Include the Filepond library -->
    <script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.js"></script>
    <script src="https://unpkg.com/filepond-plugin-image-crop/dist/filepond-plugin-image-crop.js"></script>
    <script src="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.js"></script>
    <script src="https://unpkg.com/filepond-plugin-get-file@1.0.6/dist/filepond-plugin-get-file.min.js"></script>
    <script src="https://unpkg.com/filepond@^4/dist/filepond.js"></script>
</body>

</html>
