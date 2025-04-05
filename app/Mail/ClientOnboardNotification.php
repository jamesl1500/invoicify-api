<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ClientOnboardNotification extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The user instance.
     */
    public $user;

    /**
     * The client instance.
     */
    public $client;

    /**
     * Create a new message instance.
     */
    public function __construct($user, $client)
    {
        // Store the user and client data
        $this->user = $user;
        $this->client = $client;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'You have  anew invitation to join Invoicify',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.ClientOnboardNotification',
            with: [
                'user' => $this->user,
                'client' => $this->client,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
