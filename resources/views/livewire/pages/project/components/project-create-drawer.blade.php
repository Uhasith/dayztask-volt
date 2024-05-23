<div wire:keydown.esc="$dispatch('closeDrawer')">
    <x-mary-drawer wire:model="showDrawer" right class="w-full lg:w-5/12">
        <div @click="$dispatch('closeDrawer')" class="absolute z-10 top-3 left-5 cursor-pointer flex items-center gap-5">
            <x-mary-icon name="o-arrows-right-left" />
            <label class="text-lg">Update Project Details</label>
        </div>
       
        <div class="w-full min-h-64 overflow-y-auto justify-center mt-10 px-4">
              <livewire:pages.project.components.create-project />
        </div>
    
    </x-mary-drawer>
</div>
