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

    public $start_date;

    public $end_date;

    public string $totalWorkedTime;

    #[On('startDateUpdated')]
    public function startDateUpdated($param): void
    {
        $this->start_date = $param;
        $this->end_date = null;
        $this->dispatch('pg:eventRefresh-TaskTrackTable');
    }

    #[On('endDateUpdated')]
    public function endDateUpdated($param): void
    {
        $this->end_date = $param;
        $this->dispatch('pg:eventRefresh-TaskTrackTable');
    }

    #[On('userUpdated')]
    public function userUpdated($param): void
    {
        $this->user_id = $param;
        $this->dispatch('pg:eventRefresh-TaskTrackTable');
    }

    #[On('projectUpdated')]
    public function projectUpdated($param): void
    {
        $this->project_id = $param;
        $this->dispatch('pg:eventRefresh-TaskTrackTable');
    }

    public function setUp(): array
    {
        // $this->showCheckBox();

        return [
            PowerGrid::header()
                ->showSearchInput()
                ->includeViewOnTop('components.task-tracking-table-header'),
            PowerGrid::footer()
                ->showPerPage(perPage: 25, perPageValues: [25, 50, 100])
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        $query = TaskTracking::query()
            ->join('tasks', 'task_trackings.task_id', '=', 'tasks.id')
            ->join('projects', 'tasks.project_id', '=', 'projects.id')
            ->select([
                'task_trackings.*',
                'tasks.name as task_name',
                'projects.title as project_title',
            ]);

        // Filter by user ID if not "All"
        if ($this->user_id !== 'All') {
            $query->where('task_trackings.user_id', $this->user_id);
        }

        // Filter by project ID if not "All"
        if ($this->project_id !== 'All') {
            $projectObj = Project::with('tasks')->where('id', $this->project_id)->first();

            if ($projectObj) {
                $taskIds = $projectObj->tasks->pluck('id');
                $query->whereIn('task_trackings.task_id', $taskIds);
            } else {
                $query->whereRaw('1 = 0'); // Forces an empty result set if project does not exist
            }
        }

        // Apply date filtering
        if (! empty($this->start_date) || ! empty($this->end_date)) {
            // Parse dates using Carbon and format to 'Y-m-d'
            $startDate = $this->start_date ? Carbon::parse($this->start_date)->startOfDay() : null;
            $endDate = $this->end_date ? Carbon::parse($this->end_date)->endOfDay() : Carbon::parse($this->start_date)->endOfDay();

            // Apply date range filters based on available dates
            if ($startDate && $endDate) {
                // Use whereBetween if both start and end dates are provided
                $query->whereBetween('task_trackings.created_at', [$startDate, $endDate]);
            } elseif ($startDate) {
                // Use where if only start date is provided
                $query->whereBetween('task_trackings.created_at', [$startDate, $endDate]);
            } elseif ($endDate) {
                // Use where if only end date is provided
                $query->where('task_trackings.created_at', '<=', $endDate);
            }
        }

        // Calculate the total worked time
        $totalMinutes = $query->get()->sum(function ($record) {
            $startTime = Carbon::parse($record->start_time);
            $endTime = $record->end_time ? Carbon::parse($record->end_time) : Carbon::now();

            return $startTime->diffInMinutes($endTime);
        });

        // Format the total time into hours and minutes
        $hours = floor($totalMinutes / 60);
        $minutes = $totalMinutes % 60;
        $this->totalWorkedTime = "{$hours} hours {$minutes} minutes";

        return $query;
    }

    public function relationSearch(): array
    {
        return [
            'task' => ['name'],
            'project' => ['title'],
        ];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('task_name')
            ->add('project_title')
            ->add('start_time')
            ->add('total_time', function ($record) {
                $start = Carbon::parse($record->start_time);
                $end = Carbon::parse($record->end_time) ?? Carbon::now();

                $totalMinutes = $start->diffInMinutes($end); // Total difference in minutes
                $hours = floor($totalMinutes / 60); // Calculate hours as an integer
                $minutes = $totalMinutes % 60; // Remaining minutes after hours

                if ($hours > 0) {
                    return "{$hours} hour".($hours == 1 ? '' : 's')." {$minutes} minute".($minutes == 1 ? '' : 's');
                }

                return "{$minutes} minute".($minutes == 1 ? '' : 's');
            })
            ->add('end_time');
    }

    public function columns(): array
    {
        return [
            Column::make('Id', 'id'),
            Column::make('Task Name', 'task_name', 'tasks.name')
                ->searchable()
                ->sortable(),
            Column::make('Project Title', 'project_title', 'projects.title')
                ->searchable()
                ->sortable(),
            Column::make('Start Time', 'start_time')->sortable()->searchable(),
            Column::make('End Time', 'end_time')->sortable()->searchable(),
            Column::make('Total Time', 'total_time'),
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
