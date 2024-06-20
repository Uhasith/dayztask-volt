<div class="w-full mx-auto p-5 lg:px-10 lg:py-5">
    <div class="grid grid-cols-12 items-center mb-4">
        <div class="col-span-4">
            <x-filament::button icon="heroicon-m-sparkles">
                New Task
            </x-filament::button>
        </div>
        <div class="col-span-6 flex items-center justify-end gap-5 text-end px-5">
            <x-select placeholder="Select relator" class="w-[50%]">
                <x-select.user-option src="{{ asset('assets/images/logo.png') }}" label="André Luiz" value="1" />
                <x-select.user-option src="{{ asset('assets/images/logo.png') }}" label="Fernando Gunther"
                    value="2" />
                <x-select.user-option src="{{ asset('assets/images/logo.png') }}" label="Keithyellen Huhn"
                    value="3" />
                <x-select.user-option src="{{ asset('assets/images/logo.png') }}" label="João Pedro" value="4" />
                <x-select.user-option src="{{ asset('assets/images/logo.png') }}" label="Pedro Henrique"
                    value="5" />
            </x-select>
            <x-select placeholder="Select relator" class="w-[50%]">
                <x-select.user-option src="{{ asset('assets/images/logo.png') }}" label="André Luiz" value="1" />
                <x-select.user-option src="{{ asset('assets/images/logo.png') }}" label="Fernando Gunther"
                    value="2" />
                <x-select.user-option src="{{ asset('assets/images/logo.png') }}" label="Keithyellen Huhn"
                    value="3" />
                <x-select.user-option src="{{ asset('assets/images/logo.png') }}" label="João Pedro" value="4" />
                <x-select.user-option src="{{ asset('assets/images/logo.png') }}" label="Pedro Henrique"
                    value="5" />
            </x-select>
        </div>
        <div class="col-span-2 flex items-center justify-end gap-5 text-end px-5">
            <x-filament::button size="xs">
                New Task
            </x-filament::button>
            <x-filament::button size="xs">
                New Task
            </x-filament::button>
        </div>
    </div>
    <div class="flex gap-4 items-center justify-end px-5">
        <div>
            <x-filament::button>
                <x-mary-icon name="m-document-text" />
            </x-filament::button>
        </div>
        <div>
            <x-filament::button>
                <x-mary-icon name="m-plus" />
            </x-filament::button>
        </div>
    </div>
</div>
