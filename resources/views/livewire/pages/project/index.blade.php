<?php
use App\Models\Project;
use function Livewire\Volt\{state, mount, with, usesPagination};

usesPagination();

with(fn() => ['projects' => Auth::user()->projects()->paginate(12)]);

?>

<div class="grid grid-cols-12 gap-4 overflow-y-auto p-5">
    @foreach ($projects as $key => $project)
        <div class="col-span-12 md:col-span-6 lg:col-span-4 cursor-pointer">
            <livewire:pages.project.components.project-card :project="$project" :key="'project-' . $key" />
        </div>
    @endforeach

    <div class="col-span-12">
        {{ $projects->links() }}
    </div>
</div>
