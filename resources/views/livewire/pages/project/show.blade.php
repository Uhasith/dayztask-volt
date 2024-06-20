<div class="w-full mx-auto p-5 lg:px-10 lg:py-5" x-data>
    <div class="grid grid-cols-12 items-center">
        <div class="col-span-12 md:col-span-3 px-2 md:mt-0">
            <x-wui-input icon="magnifying-glass" placeholder="Search Input" />

        </div>
        <div class="col-span-12 md:col-span-6 mt-4 md:mt-0 px-2 md:px-5 flex items-center justify-end gap-5 text-end">
            <x-wui-select placeholder="Select relator" class="w-[50%]">
                <x-wui-select.user-option src="{{ asset('assets/images/logo.png') }}" label="André Luiz" value="1" />
                <x-wui-select.user-option src="{{ asset('assets/images/logo.png') }}" label="Fernando Gunther"
                    value="2" />
                <x-wui-select.user-option src="{{ asset('assets/images/logo.png') }}" label="Keithyellen Huhn"
                    value="3" />
                <x-wui-select.user-option src="{{ asset('assets/images/logo.png') }}" label="João Pedro"
                    value="4" />
                <x-wui-select.user-option src="{{ asset('assets/images/logo.png') }}" label="Pedro Henrique"
                    value="5" />
            </x-wui-select>
            <x-wui-select placeholder="Select relator" class="w-[50%]">
                <x-wui-select.user-option src="{{ asset('assets/images/logo.png') }}" label="André Luiz"
                    value="1" />
                <x-wui-select.user-option src="{{ asset('assets/images/logo.png') }}" label="Fernando Gunther"
                    value="2" />
                <x-wui-select.user-option src="{{ asset('assets/images/logo.png') }}" label="Keithyellen Huhn"
                    value="3" />
                <x-wui-select.user-option src="{{ asset('assets/images/logo.png') }}" label="João Pedro"
                    value="4" />
                <x-wui-select.user-option src="{{ asset('assets/images/logo.png') }}" label="Pedro Henrique"
                    value="5" />
            </x-wui-select>
        </div>
        <div class="col-span-12 md:col-span-3 mt-4 md:mt-0 flex items-center justify-center gap-5">
            <x-wui-button primary label="My Tasks" />
            <x-wui-button positive label="Completed" />
            <x-wui-mini-button info icon="document-text" />
            <x-wui-mini-button primary icon="plus" />
        </div>
    </div>
</div>
