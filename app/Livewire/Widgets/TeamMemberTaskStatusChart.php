<?php

namespace App\Livewire\Widgets;

use App\Models\Project;
use App\Models\Task;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class TeamMemberTaskStatusChart extends ChartWidget
{
    protected static ?string $heading = 'Your Task Status Chart';

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

        // Get task count based on their status for the selected date range
        $data = Task::whereIn('id', $taskIds)->whereHas('users', function ($query) {
            $query->where('users.id', Auth::id());
        })
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get()
            ->groupBy('status')
            ->map(function ($tasks, $status) {
                return $tasks->count();
            });

        // Ensure all statuses are included, even if there is no data for them
        $statuses = collect(['todo', 'doing', 'done']);
        $data = $statuses->mapWithKeys(function ($status) use ($data) {
            return [$status => $data->get($status, 0)];
        });

        return [
            'labels' => $data->keys(),
            'datasets' => [
                [
                    'label' => 'Task count',
                    'data' => $data->values(),
                    'backgroundColor' => ['#FF6384', '#36A2EB', '#FFCE56'],
                    'hoverOffset' => 4,
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
