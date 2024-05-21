<div>
    <div class="grid grid-cols-12 gap-4 overflow-auto p-5">
        @foreach ($projects as $key => $project)
            <div class="col-span-12 md:col-span-6 lg:col-span-4 cursor-pointer">
                <livewire:pages.project.components.project-card :project="$project" :key="'project-' . $key" />
            </div>
        @endforeach

        <div class="col-span-12">
            {{ $projects->links() }}
        </div>
    </div>
    <livewire:pages.project.components.project-create-drawer />
</div>
