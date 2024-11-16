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
            'today' => 'Today',
            'yesterday' => 'Yesterday',
            'week' => 'Current week',
            'month' => 'Current month',
            'year' => 'Current year',
            'lastWeek' => 'Last week',
            'lastMonth' => 'Last month',
            'lastYear' => 'Last year',
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
            case 'today':
                $startDate = Carbon::now()->startOfDay();
                $endDate = Carbon::now()->endOfDay();
                break;
            case 'yesterday':
                $startDate = Carbon::yesterday()->startOfDay();
                $endDate = Carbon::yesterday()->endOfDay();
                break;
            case 'lastMonth':
                $startDate = Carbon::now()->subMonth()->startOfMonth();
                $endDate = Carbon::now()->subMonth()->endOfMonth();
                break;
            case 'lastYear':
                $startDate = Carbon::now()->subYear()->startOfYear();
                $endDate = Carbon::now()->subYear()->endOfYear();
                break;
            case 'lastWeek':
                $startDate = Carbon::now()->subWeek()->startOfWeek();
                $endDate = Carbon::now()->subWeek()->endOfWeek();
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

        // Get task tracking records for the specified date range
        $data = TaskTracking::where('user_id', Auth::id())
            ->whereIn('task_id', $taskIds)
            ->whereBetween('start_time', [$startDate, $endDate])
            ->get()
            ->groupBy(function ($record) {
                return Carbon::parse($record->start_time)->format('l'); // Group by day of the week (e.g., Monday)
            })
            ->map(function ($day) {
                // Sum the total tracked time for each day
                return $day->sum(function ($record) {
                    $startTime = Carbon::parse($record->start_time);
                    $endTime = $record->end_time ? Carbon::parse($record->end_time) : Carbon::now();

                    return round($startTime->diffInHours($endTime), 2);
                });
            });

        // Ensure all days of the week are included, even if there is no data for them
        $daysOfWeek = collect(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']);
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
