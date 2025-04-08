<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentMadeNotification extends Notification
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
     * The payment instance.
     */
    public $payment;

    /**
     * Create a new notification instance.
     */
    public function __construct($invoice, $client, $payment)
    {
        // Store the invoice, client, and payment data
        $this->invoice = $invoice;
        $this->client = $client;
        $this->payment = $payment;
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
            'invoice_id' => $this->invoice->id,
            'client_id' => $this->client->id,
            'payment_id' => $this->payment->id,
            'message' => 'You have received a payment',
        ];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Payment Received')
            ->greeting('Hello!')
            ->line("You have received a payment.")
            ->action('View Invoice', env('FRONTEND_URL') . '/invoices/' . $this->invoice->id)
            ->line('Invoice Number: ' . $this->invoice->invoice_number)
            ->line('Client Name: ' . $this->client->name)
            ->line('Payment Amount: ' . $this->payment->amount)
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
