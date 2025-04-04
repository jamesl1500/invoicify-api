<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Invoices;
use App\Models\Clients;
use App\Models\Payments;
use App\Models\User;
use App\Models\Invoices_Items;

use App\Notifications\InvoiceCreatedNotification;
use App\Mail\InvoiceCreatedNotification as InvoiceCreatedMail;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'clientId' => 'required|uuid',
            'invoiceNumber' => 'required|string|max:255',
            'invoiceDate' => 'required|date',
            'dueDate' => 'required|date',
            'items' => 'required|array',
            'subtotal' => 'required|numeric',
            'tax' => 'required|numeric',
            'taxRate' => 'required|numeric',
            'total' => 'required|numeric',
        ]);

        // Make sure the user is authenticated
        if (!$request->user()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Check if the client exists
        $client = Clients::find($validatedData['clientId']);

        if (!$client) {
            return response()->json(['error' => 'Client not found'], 404);
        }
        // Check if the user is authorized to create an invoice for this client
        if ($client->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Create a new invoice
        $invoice = new Invoices();
        $invoice->user_id = $request->user()->id;
        $invoice->client_id = $validatedData['clientId'];
        $invoice->invoice_number = $validatedData['invoiceNumber'];
        $invoice->issue_date = $validatedData['invoiceDate'];
        $invoice->due_date = $validatedData['dueDate'];
        $invoice->sub_total = $validatedData['subtotal'];
        $invoice->tax_rate = $validatedData['taxRate'];
        $invoice->tax_amount = $validatedData['tax'];
        $invoice->total_amount = $validatedData['total'];
        $invoice->status = 'pending'; // Default status

        // Save invoice
        $invoice->save();

        // Loop through the items and add them to the invoice
        foreach ($validatedData['items'] as $item) {
            $invoiceItem = new Invoices_Items();
            $invoiceItem->invoice_id = $invoice->id;
            $invoiceItem->item = $item['item'];
            $invoiceItem->description = $item['description'];
            $invoiceItem->quantity = $item['quantity'];
            $invoiceItem->unit_price = $item['price'];

            // Save the invoice item
            $invoiceItem->save();
        }

        // Create PDF
        $pdf = $this->generatePDF($invoice, $client);

        // Send notification to the client
        //$client->notify(new InvoiceCreatedNotification($invoice, $client));

        // Send email to the client
        Mail::to($client->email)->send(new InvoiceCreatedMail($invoice, $client, $pdf));

        // Return the created invoice
        return response()->json(['message' => 'Invoice created successfully', 'invoice' => $invoice], 201);
    }

    /**
     * Generate PDF
     */
    public function generatePDF(Invoices $invoice, Clients $client)
    {
        set_time_limit(300);
        Log::info('PDF Generation started');
        $start = microtime(true);

        // Create a new PDF and save it
        $pdf = Pdf::loadView('invoices.style1', [
            'invoice' => $invoice,
            'client' => $client,
        ]);

        // Set the PDF filename
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions(['defaultFont' => 'sans-serif']);

        // Save the PDF to a file
        $pdfPath = storage_path('app/public/invoices/' . $invoice->id . '.pdf');
        $pdf->save($pdfPath);

        // Update the invoice with the PDF URL
        $invoice->pdf_url = $invoice->id . '.pdf';
        $invoice->save();

        // Return the PDF file as json
        $end = microtime(true);
        $executionTime = ($end - $start) * 1000; // Convert to milliseconds
        Log::info('PDF Generation completed' . $executionTime . 'ms');  
        return response()->json(['pdf_url' => $invoice->pdf_url], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
