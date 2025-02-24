<?php

use Livewire\Volt\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    //
}; ?>

<div class="mr-2">
    <x-mary-button id="dropdownNotificationButton" data-dropdown-toggle="dropdownNotification"
        icon="o-chat-bubble-left-ellipsis" class="btn-circle btn-sm relative">
        {{-- @php
            $unreadMessagesCount = DB::table('wire_participants')->where('participantable_id', Auth::id())
            ->whereNull('conversation_read_at')->count();
        @endphp
        @if ($unreadMessagesCount > 0)
            <x-mary-badge value="{{ $unreadMessagesCount }}" class="badge-error badge-sm absolute -right-2 -top-2" />
        @endif --}}
    </x-mary-button>

    <!-- Dropdown menu -->
    <div id="dropdownNotification"
        class="z-20 hidden w-full max-w-sm bg-white/95 dark:bg-gray-900 divide-y divide-gray-100 rounded-lg shadow-sm dark:divide-gray-700"
        aria-labelledby="dropdownNotificationButton">
        <div
            class="block px-4 py-2 font-medium text-center text-gray-700 rounded-t-lg bg-gray-50 dark:bg-gray-900 dark:text-white">
            Messages
        </div>
        <div class="divide-y divide-gray-100 dark:divide-gray-700 h-[500px]">
            <livewire:wirechat.chats />
        </div>
    </div>
</div>
