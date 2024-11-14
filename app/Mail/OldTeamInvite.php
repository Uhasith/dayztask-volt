<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OldTeamInvite extends Mailable
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
        $username = $this->mailData['username'];
        $loginUrl = $this->mailData['loginUrl'];
        return new Content(
            view: 'emails.old-member-invitation',
            with: ['username' => $username, 'loginUrl' => $loginUrl]
        );
    }
}
