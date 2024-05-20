<?php

use function Livewire\Volt\{state, boot};
use Filament\Notifications\Events\DatabaseNotificationsSent;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use App\Services\NotificationService;

state(['project']);

$send = function () {
    $user = auth()->user();

    $title = 'Saved successfully';
    $body = 'Changes to the post have been saved.';
    $url = route('dashboard');

    // Use the service to send a notification
    app(NotificationService::class)->sendNotification($user, $title, $body, $url);
};

?>

<div class="relative">
    <div class="absolute z-10 block top-2 right-2 cursor-pointer">
        <svg xmlns="http://www.w3.org/2000/svg" height="16" width="4" viewBox="0 0 128 512">
            <path
                d="M64 360a56 56 0 1 0 0 112 56 56 0 1 0 0-112zm0-160a56 56 0 1 0 0 112 56 56 0 1 0 0-112zM120 96A56 56 0 1 0 8 96a56 56 0 1 0 112 0z" />
        </svg>
    </div>
    <div wire:click="send"
        class="rounded-xl shadow-xl transform hover:scale-105 transition duration-700 ease-in-out relative">
        <div style="{{ $project['bg_color'] ? 'background-color:' . $project['bg_color'] : '#eaddd7' }}"
            class="rounded-xl flex flex-wrap items-start justify-start gap-x-4 gap-y-2 p-3 hover:shadow-md transition-shadow duration-300 ease-in-out">
            <div
                class="w-full flex flex-col gap-2 items-center flex-wrap justify-center 
                text-[18px] font-semibold leading-10 tracking-tight transition-colors duration-300 ease-in-out">
                <div
                    class="bg-white/30 backdrop-opacity-10 w-full h-28 flex flex-col items-center justify-center rounded-lg p-2">
                    <h5 class="mb-2 text-xl max-w-60 font-bold tracking-tight text-gray-900 dark:text-white truncate"
                        style="{{ $project['font_color'] ? 'color:' . $project['font_color'] : '#000' }}">
                        {{ $project['title'] ?? 'No Title' }}</h5>
                    <div class="flex items-center justify-center">
                        @if (isset($project['company_logo']))
                            <img class="w-32 max-h-12 rounded-md" src="{{ $project['company_logo'] }}"
                                alt="project-logo" />
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
