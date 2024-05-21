<div wire:keydown.esc="$dispatch('closeDrawer')">
    <x-mary-drawer wire:model="showDrawer" right class="w-11/12 lg:w-1/3">
        <div @click="$dispatch('closeDrawer')" class="absolute z-10 top-3 left-5 cursor-pointer flex items-center gap-5">
            <x-mary-icon name="o-arrows-right-left" />
            <label class="text-lg">Update Project Details</label>
        </div>
        @if ($isLoading)
            <div class="w-full h-52 flex items-center justify-center mt-5">
                <x-mary-loading class="loading-bars" />
            </div>
        @else
            <div class="w-full justify-center mt-10">
                <x-mary-form wire:submit="save">
                    <x-mary-input label="Project Title" wire:model="title" />
                    <x-mary-input label="Amount" wire:model="amount" prefix="USD" money
                        hint="It submits an unmasked value" />

                    <x-slot:actions>
                        <x-mary-button label="Cancel" />
                        <x-mary-button label="Click me!" class="btn-primary" type="submit" spinner="save" />
                    </x-slot:actions>
                </x-mary-form>
            </div>
        @endif
    </x-mary-drawer>
</div>
