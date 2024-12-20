<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MissingCheckoutNotification extends Notification
{
    use Queueable;

    protected $activity;
    /**
     * Create a new notification instance.
     */
    public function __construct($activity)
    {
        $this->activity = $activity;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Missing Checkout Alert')
            ->line('You have not completed your checkout for today.')
            ->line('Checkin time: ' . date('Y-m-d h:i:s a', strtotime($this->activity->properties['checkin'])))
            ->line('Location: ' . ucwords($this->activity->properties['location']))
            ->action('Complete Checkout', url(config('app.url')))
            ->line('Please complete your checkout as soon as possible.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
