<div>
    {{-- <x-wui-select placeholder="Select a workspace" wire:model="workspaceId">
        @foreach ($workspaces as $workspace)
            <x-wui-select.option label="{{ $workspace['name'] }}" value="{{ $workspace['id'] }}" />
        @endforeach
    </x-wui-select> --}}
    <x-wui-native-select placeholder="Select a workspace" wire:model="workspaceId">
        @foreach ($workspaces as $workspace)
            <option value="{{ $workspace['id'] }}">{{ $workspace['name'] }}</option>
        @endforeach
    </x-wui-native-select>
</div>
