<?php

namespace App\Livewire\Widgets;

use App\Models\Task;
use Illuminate\Support\Facades\Auth;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        // Initialize variables for description and color
        $openTaskDescription = "";
        $openTaskColor = "";
        $openTaskIcon = "";

        // Current week task count
        $currentWeekTaskCount = Task::wherehas('users', fn ($q) => $q->where('user_id', Auth::user()->id))->where('status', 'todo')->count();

        // Last week task count
        $lastWeekTaskCount = Task::wherehas('users', fn ($q) => $q->where('user_id', Auth::user()->id))
            ->where('status', 'todo')
            ->whereBetween('created_at', [now()->subWeeks(2), now()->subWeek()])
            ->count();

        // Calculate the difference
        $taskCountChange = $currentWeekTaskCount - $lastWeekTaskCount;

        // Generate the result string and set the color
        if ($taskCountChange > 0) {
            $openTaskDescription = "+ {$taskCountChange} increased from last week";
            $openTaskColor = "success"; // Green for increase
            $openTaskIcon = "heroicon-m-arrow-trending-up";
        } elseif ($taskCountChange < 0) {
            $openTaskDescription = "- " . abs($taskCountChange) . "decreased from last week";
            $openTaskColor = "danger"; // Red for decrease
            $openTaskIcon = "heroicon-m-arrow-trending-down";
        } else {
            $openTaskDescription = "The task count remains the same";
            $openTaskColor = "warning"; // Yellow for no change
            $openTaskIcon = "heroicon-m-arrow-long-right";
        }

        return [
            Stat::make('Open tasks', $currentWeekTaskCount)
                ->description($openTaskDescription)
                ->descriptionIcon($openTaskIcon)
                ->color($openTaskColor),
            Stat::make('Bounce rate', '21%')
                ->description('7% increase')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'),
            Stat::make('Average time on page', '3:12')
                ->description('0% increase')
                ->descriptionIcon('heroicon-m-arrow-long-right')
                ->color('warning'),
            Stat::make('Unique views', '192.1k')
                ->description('32k increase')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
        ];
    }
}
