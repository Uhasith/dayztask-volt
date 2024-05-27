<div>
    <form wire:submit="save">
        {{ $this->form }}

        <x-filament::button type="submit" class="mt-10 w-full">
            Update Project
        </x-filament::button>
    </form>

    <x-filament-actions::modals />
</div>
