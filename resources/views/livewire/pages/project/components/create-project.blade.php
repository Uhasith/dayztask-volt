<div>
    <form wire:submit="create">
        {{ $this->form }}
        
        <x-filament::button type="submit" class="mt-10 w-full">
            Create Project 😃
        </x-filament::button>

    </form>

    <x-filament-actions::modals />
</div>
