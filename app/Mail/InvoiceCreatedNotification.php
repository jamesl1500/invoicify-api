<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

use App\Models\Invoices;
use App\Models\Clients;

class InvoiceCreatedNotification extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The invoice instance.
     */
    public $invoice;

    /**
     * The client instance.
     */
    public $client;

    /**
     * Create a new message instance.
     */
    public function __construct(Invoices $invoice, Clients $client)
    {
        // Store the invoice and client data
        $this->invoice = $invoice;
        $this->client = $client;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(
                "hello@invoicify.com", "Invoicify",
            ),
            subject: 'You have a new invoice',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.InvoiceCreatedEmail',
            with: [
                'invoice' => $this->invoice,
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
        return [
            // Attach the invoice PDF
            Attachment::fromPath(storage_path('app/public/invoices/' . $this->invoice->pdf_url))
                ->as('invoice.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
