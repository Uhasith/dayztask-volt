<?php

use Livewire\Volt\Component;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use App\Models\Task;
use function Livewire\Volt\{with, usesPagination};
use Carbon\Carbon;
// use App\Contracts\Sorting;
// use App\Concerns\WithSorting;
 
// uses([Sorting::class, WithSorting::class]);
// usesPagination();

new class extends Component {
    
    public $screenshots;
    public $selectedProjectIDs, $selectedTaskIDs, $selectedUserID, $selectedDate;
    public $user;
    // function with(){
    //     $screenshots = Media::where('collection_name', 'screenshot')
    //     ->where('model_type', 'App\Models\Task')
    //     // ->whereIn('model_id', auth()->user()->tasks->pluck('id'))
    //     // ->orderBy('created_at')
    //     // ->with('model')
    //     ->paginate(10);
    //     return ['screenshots' => $screenshots];
    // }

    function mount() : void {
        $this->user = auth()->user();
        $this->selectedDate = Carbon::now()->format('Y-m-d');
        $this->setScreenshots();
    }

    function updatedSelectedProjectIDs($value) : void {
        $this->selectedTaskIDs = [];
        $this->setScreenshots();
    }

    function updatedSelectedTaskIDs($value) : void {
        $this->setScreenshots();
    }

    function updatedSelectedUserID($value) : void {
        $this->user = App\Models\User::find($value);
        Log::info($value);
        $this->setScreenshots();
    }

    function updatedSelectedDate($value) : void {
        // $this->user = App\Models\User::find($value);
        // Log::info($value);
        $this->setScreenshots();
    }

    function setScreenshots() : void {
        if(empty($this->user)){
            $this->user = auth()->user();
        }

        if(!empty($this->selectedTaskIDs)){
            $taskIDs = $this->selectedTaskIDs;
        }else{
            $taskIDs = $this->user->tasks->pluck('id');
        }

        if(!empty($this->selectedProjectIDs)){
            $taskIDs = Task::whereIn('project_id', $this->selectedProjectIDs)->pluck('id');
        }



        $screenshots = Media::where('collection_name', 'screenshot')
        ->where('model_type', 'App\Models\Task')
        ->whereIn('model_id', $taskIDs)
        ->whereJsonContains('custom_properties', ['user_id' => $this->user->id])
        ->orderBy('created_at')
        ->with('model')
        ->whereRaw('date(created_at) = ?', [Carbon::parse($this->selectedDate)->format('Y-m-d')] )
        ->get()
        ->toBase()
        ->groupBy(function ($item) {
            return Carbon\Carbon::parse($item->created_at)->format('H'); // Group by hour
        });
        $this->screenshots = $screenshots;
    }
}; ?>

<div class="py-4">
    <div class="mx-auto sm:px-6 lg:px-8">

        {{-- Filters --}}
        <div class="mb-10 bg-teal-50 border border-teal-400 dark:bg-gray-800 dark:border-gray-800 p-5 rounded-lg">
            <div class="grid grid-cols-4 gap-5">
                <x-wui-select label="Filter by Projects" placeholder="Select Projects" wire:model.live="selectedProjectIDs" multiselect :async-data="route('projects.search')" option-label="title" option-value="id" />
                <x-wui-select label="Filter by Tasks" placeholder="Select Tasks" wire:model.live="selectedTaskIDs" multiselect :async-data="route('tasks.search')" option-label="name" option-value="id" />
                <x-wui-select icon="user" label="Filter by Users" placeholder="User" class="w-[50%]"
                        wire:model.live="selectedUserID">
                        @foreach (auth()->user()->currentTeam->allUsers() as $key => $member)
                            <x-wui-select.user-option
                                src="{{ !empty($member['profile_photo_path']) ? asset('storage/' . $member['profile_photo_path']) : asset('assets/images/no-user-image.png') }}"
                                label="{{ $member['name'] }}" value="{{ $member['id'] }}"
                                wire:key="{{ 'filter-option-' . $key }}" />
                        @endforeach
                    </x-wui-select>

                    <div>
                        <x-wui-datetime-picker
                            wire:model.live="selectedDate"
                            label="Filter by Date"
                            placeholder="Select a Date"
                            max="{{ Carbon::now()->format('Y-m-d') }}"
                            without-timezone
                            without-time
                        />
                    </div>
            </div>
        </div>

        <ol class="relative border-s border-gray-200 dark:border-gray-700">
            @foreach ($screenshots as $hour => $items)
                <li class="mb-10 ms-4">
                    <div class="absolute w-3 h-3 bg-gray-200 rounded-full mt-1.5 -start-1.5 border border-white dark:border-gray-900 dark:bg-gray-700"></div>
                    <p class="mb-3 text-base font-semibold leading-none text-gray-800 dark:text-gray-500 uppercase">{{ Carbon\Carbon::parse($hour . ':00')->format('h:i a') }} - {{ Carbon\Carbon::parse($hour . ':00')->addHours(1)->format('h:i a') }}</p>
                    <div class="grid grid-cols-6 gap-5">
                        @foreach ($items as $screenshot)
                            <div class="media-item">
                                <div class="border rounded-lg border-teal-200 px-5 py-3 bg-slate-50 dark:bg-gray-800 dark:border-gray-700">
                                    <a data-fslightbox href="{{$screenshot->getUrl()}}" class="mb-4">
                                        <img src="{{$screenshot->getUrl()}}" alt="Image" class="rounded-lg w-full h-32 object-cover" />
                                    </a>
                                    <div class="flex gap-3 items-center justify-between">
                                    
                                    <a href="{{route('projects.tasks.update', $screenshot->model->uuid)}}" class="font-semibold text-gray-900 dark:text-white">{{$screenshot->model->name}}</a>
                                    
                                    <div class="flex gap-1"><x-wui-icon data-tooltip-target="tooltip-{{$screenshot->id}}" name="clock" class="w-5 h-5" solid />
                                        @if ($screenshot->getCustomProperty('display_count'))
                                            <x-wui-icon data-tooltip-target="tooltip-displays-{{$screenshot->id}}" name="computer-desktop" class="w-5 h-5" solid />    
                                        @endif
                                    </div>

                                    <div id="tooltip-{{$screenshot->id}}" role="tooltip" class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
                                        {{$screenshot->created_at->format(config('tracker.datetime.format'))}}
                                        <div class="tooltip-arrow" data-popper-arrow></div>
                                    </div>

                                    <div id="tooltip-displays-{{$screenshot->id}}" role="tooltip" class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
                                        {{$screenshot->getCustomProperty('display_index') . ' of ' . $screenshot->getCustomProperty('display_count')}}
                                        <div class="tooltip-arrow" data-popper-arrow></div>
                                    </div>
                                </div>
                                    @if ($screenshot->getCustomProperty('user_id'))
                                        <p class="text-xs mt-2 font-normal text-gray-500 dark:text-gray-400">{{App\Models\User::find($screenshot->getCustomProperty('user_id'))->name}}</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </li>
            @endforeach
        </ol>
        {{-- {{ $screenshots->links() }} --}}
    </div>
    @push('scripts')
        <script src="{{asset('/js/fslightbox.js')}}"></script>
    @endpush
</div>