<?php

namespace App\Livewire\Tables;

use App\Models\Project;
use App\Models\TaskTracking;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Livewire\Attributes\On;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class TaskTrackTable extends PowerGridComponent
{
    public string $tableName = 'TaskTrackTable';

    public $user_id;

    public $project_id;

    #[On('userUpdated')]
    public function userUpdated($param): void
    {
        $this->user_id = $param;
        $this->dispatch('pg:eventRefresh-DishTable');
    }

    #[On('projectUpdated')]
    public function projectUpdated($param): void
    {
        $this->project_id = $param;
        $this->dispatch('pg:eventRefresh-DishTable');
    }

    public function setUp(): array
    {
        // $this->showCheckBox();

        return [
            PowerGrid::header()
                ->showSearchInput(),
            PowerGrid::footer()
                ->showPerPage(perPage: 25, perPageValues: [25, 50, 100])
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        $query = TaskTracking::query()->with('task', 'project');

        // Check if "All" users or a specific user
        if ($this->user_id !== 'All') {
            $query->where('user_id', $this->user_id);
        }

        // Check if "All" projects or a specific project
        if ($this->project_id !== 'All') {
            $projectObj = Project::with('tasks')->where('id', $this->project_id)->first();

            if ($projectObj) {
                $taskIds = $projectObj->tasks->pluck('id');
                $query->whereIn('task_id', $taskIds);
            } else {
                // If project does not exist, return an empty result
                $query->whereRaw('1 = 0'); // Forces an empty result set
            }
        }

        return $query;
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('task.name')
            ->add('project.title')
            ->add('start_time')
            ->add('total_time', function ($record) {
                $start = Carbon::parse($record->start_time);
                $end = Carbon::parse($record->end_time) ?? Carbon::now();

                $totalMinutes = $start->diffInMinutes($end); // Total difference in minutes
                $hours = floor($totalMinutes / 60); // Calculate hours
                $minutes = $totalMinutes % 60; // Remaining minutes after hours

                $hoursFormatted = number_format($hours, 2);  // Format hours to two digits if hours are shown
                $minutesFormatted = $minutes; // No formatting for minutes

                if ($hours > 0) {
                    return "{$hoursFormatted} hour".($hours == 1 ? '' : 's')." {$minutesFormatted} minute".($minutes == 1 ? '' : 's');
                }

                return "{$minutesFormatted} minute".($minutes == 1 ? '' : 's');
            })
            ->add('end_time');
    }

    public function columns(): array
    {
        return [
            Column::make('Id', 'id'),
            Column::make('Task Name', 'task.name')->sortable()->searchable(),
            Column::make('Project Name', 'project.title')->sortable()->searchable(),
            Column::make('Start Time', 'start_time')->sortable()->searchable(),
            Column::make('End Time', 'end_time')->sortable()->searchable(),
            Column::make('Total Time', 'total_time')->sortable()->searchable(),
            // Column::action('Action')
        ];
    }

    public function filters(): array
    {
        return [];
    }

    // #[\Livewire\Attributes\On('edit')]
    // public function edit($rowId): void
    // {
    //     $this->js('alert(' . $rowId . ')');
    // }

    // public function actions(TaskTracking $row): array
    // {
    //     return [
    //         Button::add('edit')
    //             ->slot('Edit: ' . $row->id)
    //             ->id()
    //             ->class('pg-btn-white dark:ring-pg-primary-600 dark:border-pg-primary-600 dark:hover:bg-pg-primary-700 dark:ring-offset-pg-primary-800 dark:text-pg-primary-300 dark:bg-pg-primary-700')
    //             ->dispatch('edit', ['rowId' => $row->id])
    //     ];
    // }

    /*
    public function actionRules($row): array
    {
       return [
            // Hide button edit for ID 1
            Rule::button('edit')
                ->when(fn($row) => $row->id === 1)
                ->hide(),
        ];
    }
    */
}
