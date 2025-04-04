<?php

namespace App\Notifications;

use App\Models\Invoices;
use App\Models\Clients;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

use App\Mail\InvoiceCreatedNotification as InvoiceCreatedMail;

class InvoiceCreatedNotification extends Notification
{
    use Queueable;

    /**
     * The invoice instance.
     */
    public Invoices $invoice;

    /**
     * The client instance.
     */
    public Clients $client;

    /**
     * Create a new notification instance.
     */
    public function __construct(Invoices $invoice, Clients $client)
    {
        // Store the invoice and client data
        $this->invoice = $invoice;
        $this->client = $client;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get thew database representation of the notification.
     * 
     * @return array<string, mixed>
     * 
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'invoice_id' => $this->invoice->id,
            'client_id' => $this->client->id,
            'message' => 'You have a new invoice',
        ];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('You have a new invoice')
            ->line('Dear ' . $this->client->name . ',')
            ->line('An invoice has been created for you.')
            ->action('View Invoice', url('/invoices/' . $this->invoice->id))
            ->line('Thank you for using our application!');
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
