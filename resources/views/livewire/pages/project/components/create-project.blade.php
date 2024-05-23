<div>
    <form wire:submit="create">
        {{ $this->form }}

        <x-button type="submit" class="btn-primary mt-5 w-full">
            <span class="text-center w-full">Create Project 😃</span>
        </x-button>
    </form>

    {{-- <x-filament-actions::modals /> --}}
</div>
