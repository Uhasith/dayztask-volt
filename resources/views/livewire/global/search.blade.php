<?php

use Livewire\Volt\Component;
use App\Models\Task;
use App\Models\Project;
use Illuminate\Support\Facades\Log;

new class extends Component {
    public $search = '';
    public $commandItems = [];

    public function updatedSearch()
    {
        Log::info("Search: {$this->search}");
        // Fetch tasks and projects based on search, limit each to 5 results
        $tasks = Task::where('name', 'like', '%' . $this->search . '%')
            ->limit(5)
            ->get();
        $projects = Project::where('title', 'like', '%' . $this->search . '%')
            ->limit(5)
            ->get();

        // Format tasks and projects with icons
        $this->commandItems = [
            'tasks' => $tasks
                ->map(
                    fn($task) => [
                        'title' => $task->name,
                        'url' => route('projects.tasks.update', $task->uuid),
                        'value' => 'task-' . $task->id,
                        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                                    </svg>',
                        'default' => false,
                        'category' => 'tasks',
                    ],
                )
                ->toArray(),
            'projects' => $projects
                ->map(
                    fn($project) => [
                        'title' => $project->title,
                        'url' => route('projects.show', $project->uuid),
                        'value' => 'project-' . $project->id,
                        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                                    </svg>',
                        'default' => false,
                        'category' => 'projects',
                    ],
                )
                ->toArray(),
        ];

        Log::info($this->commandItems);
    }
}; ?>

<div wire:ignore x-data="{
    commandOpen: false,
    commandItems: $wire.entangle('commandItems').live || { tasks: [], projects: [] },
    commandItemsFiltered: [],
    commandItemActive: null,
    commandItemSelected: null,
    commandId: $id('command'),
    commandSearch: $wire.entangle('search').live || '',
    commandSearchIsEmpty() {
        return !this.commandSearch || this.commandSearch.length === 0;
    },
    commandItemIsActive(item) {
        return this.commandItemActive && this.commandItemActive.value === item.value;
    },
    updateActiveItem(index) {
        this.commandItemActive = this.commandItemsFiltered[index];
        this.commandScrollToActiveItem();
    },
    commandItemActiveNext() {
        let index = this.commandItemsFiltered.indexOf(this.commandItemActive);
        if (index < this.commandItemsFiltered.length - 1) {
            this.updateActiveItem(index + 1);
        }
    },
    commandItemActivePrevious() {
        let index = this.commandItemsFiltered.indexOf(this.commandItemActive);
        if (index > 0) {
            this.updateActiveItem(index - 1);
        }
    },
    commandScrollToActiveItem() {
        if (this.commandItemActive) {
            let activeElement = document.getElementById(this.commandItemActive.value + '-' + this.commandId);
            if (activeElement) {
                let newScrollPos = (activeElement.offsetTop + activeElement.offsetHeight) - this.$refs.commandItemsList.offsetHeight;
                this.$refs.commandItemsList.scrollTop = newScrollPos > 0 ? newScrollPos : 0;
            }
        }
    },
    commandSearchItems() {
        let searchTerm = this.commandSearch.toLowerCase();
        this.commandItemsFiltered = this.commandSearchIsEmpty()
            ? [...this.commandItems.tasks.filter(item => item.default), ...this.commandItems.projects.filter(item => item.default)]
            : [...this.commandItems.tasks.filter(item => item.title.toLowerCase().includes(searchTerm)),
               ...this.commandItems.projects.filter(item => item.title.toLowerCase().includes(searchTerm))];
        this.commandItemActive = this.commandItemsFiltered[0] || null;
    },
    commandShowCategory(item, index) {
        return index === 0 || item.category !== this.commandItemsFiltered[index - 1].category;
    },
    commandCategoryCapitalize(string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
    }
}" 
x-init="
    $watch('commandItems', () => commandSearchItems());
    $watch('commandSearch', () => commandSearchItems());
    commandItemsFiltered = commandItems;
    commandItemActive = commandItems[0];
    $watch('commandItemSelected', item => {
        if (item?.url) window.location.href = item.url;
    });
    $watch('commandOpen', value => {
        if (value) {
            document.body.classList.add('overflow-hidden');
            $nextTick(() => { window.dispatchEvent(new CustomEvent('command-input-focus', {})); });
        } else {
            document.body.classList.remove('overflow-hidden');
        }
    });
" 
@keydown.escape.window="commandOpen = false" 
@keydown.down.window="commandItemActiveNext()" 
@keydown.up.window="commandItemActivePrevious()" 
@keydown.enter.window="commandItemSelected = commandItemActive" 
@command-input-focus.window="$refs.commandInput.focus();">
    <!-- Input Field -->
    <div class="relative w-full">
        <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
            <x-mary-icon name="o-magnifying-glass" />
        </div>
        <input type="text" id="simple-search" @click="commandOpen = true"
            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
            placeholder="Cmd + G" />
    </div>

    <!-- Command List -->
    <div x-show="commandOpen" class="relative z-50 w-auto h-auto">
        <template x-teleport="body">
            <div x-show="commandOpen"
                class="fixed top-0 left-0 z-[99] flex items-center justify-center w-screen h-screen" x-cloak>
                <div x-show="commandOpen" @click="commandOpen = false" class="absolute inset-0 w-full h-full bg-black bg-opacity-40"></div>
                <div x-show="commandOpen" x-trap.inert.noscroll="commandOpen" class="flex min-h-[370px] justify-center w-full max-w-xl items-start relative" x-cloak>
                    <div class="box-border flex flex-col w-full h-full overflow-hidden bg-white rounded-md shadow-md bg-opacity-90 drop-shadow-md backdrop-blur-sm">
                        <div class="flex items-center px-3 border-b border-gray-300">
                            <svg class="w-4 h-4 mr-0 text-neutral-400 shrink-0" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8"></circle>
                                <line x1="21" x2="16.65" y1="21" y2="16.65"></line>
                            </svg>
                            <input type="text" x-ref="commandInput" x-model="commandSearch"
                                class="flex w-full px-2 py-3 text-sm bg-transparent border-0 rounded-md outline-none focus:outline-none focus:ring-0 focus:border-0 placeholder:text-neutral-400 h-11"
                                placeholder="Type a command or search..." autocomplete="off" autocorrect="off" spellcheck="false">
                        </div>
                        <div x-ref="commandItemsList" class="max-h-[320px] overflow-y-auto overflow-x-hidden">
                            <template x-for="(item, index) in commandItemsFiltered" :key="'item-' + index">
                                <div class="pb-1 space-y-1">
                                    <template x-if="commandShowCategory(item, index)">
                                        <div class="px-1 overflow-hidden text-gray-700">
                                            <div class="px-2 py-1 my-1 text-xs font-medium text-neutral-500" x-text="commandCategoryCapitalize(item.category)"></div>
                                        </div>
                                    </template>
                                    <div class="px-1">
                                        <div :id="item.value + '-' + commandId" @click="commandItemSelected = item"
                                            @mousemove="commandItemActive = item"
                                            :class="{ 'bg-blue-600 text-white': commandItemIsActive(item) }"
                                            class="relative flex gap-2 cursor-default select-none items-center rounded-md px-2 py-1.5 text-sm outline-none">
                                            <span x-html="item.icon"></span>
                                            <span x-text="item.title"></span>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>
