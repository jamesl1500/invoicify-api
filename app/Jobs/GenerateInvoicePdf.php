<?php

namespace App\Jobs;

use Barryvdh\DomPDF\PDF;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateInvoicePdf implements ShouldQueue
{
    use Queueable;

    /**
     * The invoice instance.
     */
    public $invoice;

    /**
     * Create a new job instance.
     */
    public function __construct($invoice)
    {
        // Store the invoice data
        $this->invoice = $invoice;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Generate the PDF for the invoice
        $pdf = app(PDF::class)->loadView('invoices.pdf', [
            'invoice' => $this->invoice,
        ]);

        // Save the PDF to a file
        $pdfPath = storage_path('app/public/invoices/' . $this->invoice->id . '.pdf');
        $pdf->save($pdfPath);


    }
}
