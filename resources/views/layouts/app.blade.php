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
    <link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css"
        rel="stylesheet" />

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
                <x-mary-menu-item title="Dashboard" icon="o-home" link="{{ route('dashboard') }}" />
                <x-mary-menu-item title="Projects" icon="o-presentation-chart-line"
                    link="{{ route('projects.index') }}" />
                @if (auth()->user()->hasTeamRole(auth()->user()->currentTeam, 'admin'))
                    <x-mary-menu-item title="Summary" icon="o-chart-bar" link="{{ route('summary.index') }}" />
                    <livewire:pages.checklist.components.checklist-sidebar-icon />
                @endif
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
