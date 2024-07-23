<?php

namespace App\Livewire\Widgets;

use App\Models\Project;
use App\Models\Task;
use App\Models\TaskTracking;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class TeamMemberWeekTimeChart extends ChartWidget
{
    protected static ?string $heading = 'Your Task Tracking Chart';

    protected static string $color = 'info';

    protected static ?string $pollingInterval = '5s';

    protected static ?string $maxHeight = '35vh';

    public ?string $filter = 'week';

    protected function getFilters(): ?array
    {
        return [
            'week' => 'Last week',
            'month' => 'Last month',
            'year' => 'This year',
        ];
    }

    protected function getDateRange(string $filter): array
    {
        switch ($filter) {
            case 'month':
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
                break;
            case 'year':
                $startDate = Carbon::now()->startOfYear();
                $endDate = Carbon::now()->endOfYear();
                break;
            case 'week':
            default:
                $startDate = Carbon::now()->startOfWeek();
                $endDate = Carbon::now()->endOfWeek();
                break;
        }

        return [$startDate, $endDate];
    }

    protected function getData(): array
    {
        $activeFilter = $this->filter;
        [$startDate, $endDate] = $this->getDateRange($activeFilter);

        $projectIds = Project::where('workspace_id', Auth::user()->current_workspace_id)->pluck('id');
        $taskIds = Task::whereIn('project_id', $projectIds)->pluck('id');

        // Get task tracking records for the current week
        $data = TaskTracking::where('user_id', Auth::id())->whereIn('task_id', $taskIds)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get()
            ->groupBy(function ($record) {
                return Carbon::parse($record->created_at)->format('l'); // Group by day of the week (e.g., Monday)
            })
            ->map(function ($day) {
                // Sum the total tracked time for each day
                return $day->sum(function ($record) {
                    $startTime = Carbon::parse($record->created_at);
                    $endTime = $record->end_time ? Carbon::parse($record->updated_at) : Carbon::now();

                    return round($startTime->diffInHours($endTime), 2);
                });
            });

        // Ensure all days of the week are included, even if there is no data for them
        $daysOfWeek = collect(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']);
        $data = $daysOfWeek->mapWithKeys(function ($day) use ($data) {
            return [$day => $data->get($day, 0)];
        });

        return [
            'datasets' => [
                [
                    'label' => 'Tracked time (hours)',
                    'data' => $data->values(),
                    'backgroundColor' => '#36A2EB',
                    'borderColor' => '#9BD0F5',
                ],
            ],
            'labels' => $data->keys(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
