<?php

use Livewire\Volt\Component;
use Carbon\Carbon;
use Spatie\Activitylog\Models\Activity;
use Livewire\Attributes\Validate;
use App\Services\User\CheckInOutService;

new class extends Component {
    public bool $checked_in;
    public $todayCheckin;

    #[Validate('required')]
    public $day_end_update = '';

    function mount(): void
    {
        $user = Auth::user();
        $this->todayCheckin = app(CheckInOutService::class)->fetchTodaysCheckin($user);
        $this->checked_in = $this->todayCheckin ? true : false;
    }

    function setCheckStatus($location): void
    {
        $user = auth()->user();
        $this->todayCheckin = app(CheckInOutService::class)->setCheckStatus($location, $user);
        $this->checked_in = true;
    }

    function updateCheckout(): void
    {
        $this->validate();

        $user = auth()->user();
        $todayCheckin = Cache::pull('checkin' . $user->id);
        if ($todayCheckin) {
            app(CheckInOutService::class)->updateCheckout($user, $todayCheckin, ['day_end_update' => $this->day_end_update]);
            $this->checked_in = false;
        } else {
            if (app(CheckInOutService::class)->fetchTodaysCheckin($user) && $this->checked_in) {
                $this->updateCheckout();
            }
        }
        $this->dispatch('close-modal', id: 'dayEndModal');
    }
}; ?>
<div x-cloak class="flex items-center">
    <div class="flex gap-8 items-center justify-center">
        <div x-data="{ checkin: $wire.entangle('checked_in'), show: true }">
            <div x-show="!checkin">
                <x-wui-button x-show="show" label="Check-in" x-on:click="show = !show" right-icon="finger-print" positive
                    interaction="positive" />
                <div x-show="!show" class="flex gap-1">
                    <x-wui-button label="Home" wire:click="setCheckStatus('home')" right-icon="home" flat
                        hover="warning" focus:solid.gray interaction:solid="warning" />
                    <x-wui-button label="Office" wire:click="setCheckStatus('office')" right-icon="building-office" flat
                        hover="positive" focus:solid.green interaction:solid="positive" />
                </div>
            </div>
            <div x-show="checkin">
                <x-wui-button label="Check-out" type="button"
                    x-on:click="$dispatch('open-modal', { id: 'dayEndModal' })" right-icon="finger-print" negative
                    interaction="negative" />
            </div>
        </div>
        <div class="flex flex-col gap-1 items-center justify-center">
            @if (isset($todayCheckin?->properties['checkin']))
                <span
                    class="text-xs">{{ __('Checked in: ') . date('Y-m-d h:i:sa', strtotime($todayCheckin?->properties['checkin'])) }}</span>
            @endif
            <div>
                <livewire:global.working-task-timer />
            </div>
        </div>
    </div>

    @if (isset($todayCheckin?->properties['checkin']))
        <x-filament::modal id="dayEndModal" slide-over width="3xl">
            <form class="mb-6 p-4" wire:submit="updateCheckout">
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div>
                        <p>{{ __('Checked in at') }}</p>
                        <p class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                            {{ date('Y-m-d h:i:sa', strtotime($todayCheckin?->properties['checkin'])) }}
                        </p>
                    </div>
                    <div class="text-right">
                        <p>{{ __('Checking out at') }}</p>
                        <p class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                            {{ date('Y-m-d h:i:sa') }}
                        </p>
                    </div>
                </div>
                <div class="mb-10">
                    @php
                        $start = new DateTime($todayCheckin->properties['checkin']);
                        $end = new DateTime(date('Y-m-d h:i:sa'));
                        $diff = $end->diff($start);
                    @endphp
                    <span
                        class="mx-auto text-center bg-gray-100 text-gray-800 font-medium px-3 py-2 rounded dark:bg-gray-700 dark:text-gray-300">{{ $diff->format('%a
                                                                    Day and %h hours %i mins') }}</span>
                </div>
                <div class="mb-6">
                    <x-wui-textarea wire:model="day_end_update" rows="14" label="{{ __('Ending update') }}"
                        placeholder="Write your notes" />
                </div>
            </form>
            <x-slot name="footer">
                <div class="grid grid-cols-2 gap-x-4">
                    <x-wui-button class="w-full" solid slate label="Cancel" x-on:click="close" />
                    <x-wui-button class="w-full" solid negative label="{{ __('Checkout') }}"
                        wire:click="updateCheckout" />
                </div>
            </x-slot>
        </x-filament::modal>
    @endif

</div>
