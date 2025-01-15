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

    <!-- Styles -->
    @livewireStyles
</head>

<body>
    <div class="font-sans text-gray-900 dark:text-gray-100 antialiased">
        {{ $slot }}
    </div>
    @filamentScripts
    @livewireScripts

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('onlineUsers', {
                users: [],
                setUsers(users) {
                    this.users = users;
                },
                addUser(userId) {
                    if (!this.users.includes(userId)) {
                        this.users.push(userId);
                    }
                },
                removeUser(userId) {
                    this.users = this.users.filter(id => id !== userId);
                },
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.Echo) {
                window.Echo.join('online-users')
                    .here((users) => {
                        console.log("Initial users:", users);
                        Alpine.store("onlineUsers").setUsers(users.map(user => user.id));
                    })
                    .joining((user) => {
                        console.log("User joined:", user);
                        Alpine.store("onlineUsers").addUser(user.id);
                    })
                    .leaving((user) => {
                        console.log("User left:", user);
                        Alpine.store("onlineUsers").removeUser(user.id);
                    });
            } else {
                console.error("Echo is not initialized.");
            }
        });
    </script>
</body>

</html>
