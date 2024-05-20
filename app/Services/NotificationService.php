<?php

namespace App\Services;

use Filament\Notifications\Events\DatabaseNotificationsSent;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Notifications\Actions\Action;

class NotificationService
{
    public function sendNotification($user, $title, $body, $url)
    {
        FilamentNotification::make()
            ->title($title)
            ->success()
            ->body($body)
            ->actions([
                Action::make('view')
                    ->button()
                    ->url($url),
            ])
            ->send()
            ->sendToDatabase($user);

        event(new DatabaseNotificationsSent($user));
    }
}
