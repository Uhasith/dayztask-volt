<div class="mt-5">
    <x-filament::input.wrapper>
    <x-filament::input
        type="text"
        wire:model.live="name"
    />
    </x-filament::input.wrapper>

    @if(!empty($this->suggestions))
        <div class="suggestions-dropdown">
            <ul>
                @foreach($this->suggestions as $suggestion)
                    <li wire:click="selectSuggestion('{{ $suggestion['name'] }}')">{{ $suggestion['name'] }}</li>
                @endforeach
            </ul>
        </div>
    @endif
</div>

<style>
.suggestions-dropdown {
    border: 1px solid #ccc;
    background: white;
    position: absolute;
    z-index: 1000;
    width: 100%;
}

.suggestions-dropdown ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.suggestions-dropdown li {
    padding: 10px;
    cursor: pointer;
}

.suggestions-dropdown li:hover {
    background: #f0f0f0;
}
</style>

