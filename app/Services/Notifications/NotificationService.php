<?php

namespace App\Services\Notifications;

use Filament\Notifications\Actions\Action;
use Filament\Notifications\Events\DatabaseNotificationsSent;
use Filament\Notifications\Notification as FilamentNotification;

class NotificationService
{
    public function sendDBNotification($user, $title, $body)
    {
        FilamentNotification::make()
            ->title($title)
            ->success()
            ->body($body)
            ->actions([
                Action::make('view')
                    ->button()
                    ->url(route('dashboard')),
            ])
            ->persistent()
            ->send()
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
