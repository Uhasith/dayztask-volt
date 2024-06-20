<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <x-mary-form wire:submit="save">
                    <x-mary-input label="Name" wire:model="name" />
                    <x-mary-input label="Amount" wire:model="amount" prefix="USD" money
                        hint="It submits an unmasked value" />

                    <x-mary-slot:actions>
                        <x-mary-button label="Cancel" />
                        <x-mary-button label="Click me!" class="btn-primary" type="submit" spinner="save" />
                    </x-mary-slot:actions>
                </x-mary-form>
            </div>
        </div>
    </div>
</x-app-layout>
