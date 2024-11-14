<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewTeamInvite extends Mailable
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
        $email = $this->mailData['email'];
        $password = $this->mailData['password'];
        $loginUrl = $this->mailData['loginUrl'];

        return new Content(
            view: 'emails.new-member-invitation',
            with: ['username' => $username, 'email' => $email, 'password' => $password, 'loginUrl' => $loginUrl]
        );
    }
}
