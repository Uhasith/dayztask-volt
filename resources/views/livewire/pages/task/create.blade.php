<div class="w-full mx-auto p-5 lg:px-10 lg:py-5">
    <form wire:submit="create">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <x-wui-input icon="document-text" label="Task Name" placeholder="Task Name"  wire:model="name" />
            <x-wui-select icon="user" label="Assign To" placeholder="Assign To" class="w-[50%]"
                wire:model="assignTo" multiselect>
                @foreach ($teamMembers as $key => $member)
                    <x-wui-select.user-option
                        src="{{ !empty($member['profile_photo_path']) ? asset($member['profile_photo_path']) : asset('assets/images/no-user-image.png') }}"
                        label="{{ $member['name'] }}" value="{{ $member['id'] }}"
                        wire:key="{{ 'filter-option-' . $key }}" />
                @endforeach
            </x-wui-select>
            <div>
                <label for="description" class="block mb-1 text-sm font-medium disabled:opacity-60 text-gray-700 dark:text-gray-400 invalidated:text-negative-600 dark:invalidated:text-negative-700">Description</label>
                <livewire:global.quill :value="$description">
            </div>
        </div>
    </form>
</div>
