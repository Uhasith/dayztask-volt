<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $mailData;

    /**
     * Create a new message instance.
     */
    public function __construct($mailData)
    {
        $this->mailData = $mailData;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->mailData['email_subject']
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $email_body = $this->mailData['email_body'];
        $task = $this->mailData['task'];
        $user = $this->mailData['user'];
        $email_subject = $this->mailData['email_subject'];
        $taskUrl = env('APP_URL').'/projects/tasks/update/'.$task->uuid;

        return new Content(
            view: 'emails.notificationEmail',
            with: ['email_body' => $email_body, 'task' => $task, 'user' => $user, 'email_subject' => $email_subject, 'taskUrl' => $taskUrl]
        );
    }
}
