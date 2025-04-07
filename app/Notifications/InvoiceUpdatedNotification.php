<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoiceUpdatedNotification extends Notification
{
    use Queueable;

    /**
     * The invoice instance.
     */
    public $invoice;

    /**
     * The client instance.
     */
    public $client;

    /**
     * Create a new notification instance.
     */
    public function __construct($invoice, $client)
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
     * Get the database representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'message' => 'Your invoice has been updated.',
            'invoice_id' => $this->invoice->id,
        ];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Invoice Updated')
            ->greeting('Hello ' . $this->client->name)
            ->line('Your invoice has been updated.')
            ->action('View Invoice', env('FRONTEND_URL') . '/client/invoices/view/' . $this->invoice->id)
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
