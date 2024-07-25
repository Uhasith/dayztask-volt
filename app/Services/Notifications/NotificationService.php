<?php

namespace App\Services\Notifications;

use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Events\DatabaseNotificationsSent;
use Filament\Notifications\Notification as FilamentNotification;

class NotificationService
{
    public function sendUserTaskDBNotification($user, $title, $body, $taskId, $buttonText = 'View Task')
    {
        // Retrieve the task
        $task = Task::findOrFail($taskId);

        // Generate the route to handle the button click
        $taskRoute = route('update.user.team.workspace', $task->uuid);

        // Send the database notification with the view button
        $this->sendDBNotificationWithAction($user, $title, $body, $taskRoute, $buttonText);

    }

    public function sendDBNotificationWithAction($user, $title, $body, $route = 'dashboard', $buttonText)
    {
        FilamentNotification::make()
            ->title($title)
            ->success()
            ->body($body)
            ->actions([
                Action::make($buttonText)
                    ->button()
                    ->url($route),
            ])
            ->persistent()
            ->broadcast($user)
            ->sendToDatabase($user);

        event(new DatabaseNotificationsSent($user));
    }

    public function sendDBNotificationWithoutAction($user, $title, $body)
    {
        FilamentNotification::make()
            ->title($title)
            ->success()
            ->body($body)
            ->persistent()
            ->broadcast($user)
            ->sendToDatabase($user);

        event(new DatabaseNotificationsSent($user));
    }

    public function sendExeptionNotification()
    {
        FilamentNotification::make()
            ->title('Something Went Wrong')
            ->danger()
            ->body('Please contact support team to resolve this issue.')
            ->send();

    }

    public function sendSuccessNotification($message)
    {
        FilamentNotification::make()
            ->title('Success')
            ->success()
            ->body($message)
            ->send();
    }
}
