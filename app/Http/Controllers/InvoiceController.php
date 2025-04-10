<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Invoices;
use App\Models\Clients;
use App\Models\Payments;
use App\Models\User;
use App\Models\Invoices_Items;

use App\Notifications\InvoiceCreatedNotification;
use App\Notifications\InvoiceUpdatedNotification;
use App\Mail\InvoiceCreatedNotification as InvoiceCreatedMail;
use App\Models\Invoice_Activity;
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
        // Make sure the user is authenticated
        if (!$user = auth()->user()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Get the invoices for the authenticated user
        $invoices = Invoices::where('user_id', $user->id)->with(['client', 'user'])->get();

        // Check if the user has any invoices
        if ($invoices->isEmpty()) {
            return response()->json(['message' => 'No invoices found', 'invoices' => array()], 404);
        }

        // Lets get overdue invoices
        $overdueInvoices = Invoices::where('user_id', $user->id)
            ->where('due_date', '<', now())
            ->where('status', '!=', 'paid')
            ->with(['client', 'user'])
            ->get();
        $overdueInvoicesAmount = 0;

        foreach ($overdueInvoices as $invoice) {
            $overdueInvoicesAmount += $invoice->total_amount;
        }

        // Get paid invoices amout
        $paidInvoices = Invoices::where('user_id', $user->id)
            ->where('status', 'paid')
            ->with(['client', 'user'])
            ->get();
        $paidInvoicesAmount = 0;

        foreach ($paidInvoices as $invoice) {
            $paidInvoicesAmount += $invoice->total_amount;
        }

        // Get unpaid invoices amount
        $unpaidInvoices = Invoices::where('user_id', $user->id)
            ->where('status', '!=', 'paid')
            ->with(['client', 'user'])
            ->get();

        $unpaidInvoicesAmount = 0;

        foreach ($unpaidInvoices as $invoice) {
            $unpaidInvoicesAmount += $invoice->total_amount;
        }

        // Return the invoices as a JSON response
        return response()->json(['invoices' => $invoices, 
        'numbers' => array('paid' => $paidInvoicesAmount, 'unpaid' => $unpaidInvoicesAmount, 'overdue' => $overdueInvoicesAmount),
        'overdueInvoices' => $overdueInvoices,
        'paidInvoices' => $paidInvoices,
        'unpaidInvoices' => $unpaidInvoices], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'clientId' => 'required|exists:clients,id',
            'invoiceNumber' => 'required|string|max:255',
            'invoiceDate' => 'required|date',
            'dueDate' => 'required|date',
            'items' => 'required|array',
            'subtotal' => 'required|numeric',
            'tax' => 'required|numeric',
            'taxRate' => 'required|numeric',
            'total' => 'required|numeric',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
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
        $invoice->notes = $request->input('notes', null);
        $invoice->terms = $request->input('terms', null);
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
        $client->notify(new InvoiceCreatedNotification($invoice, $client));

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
        // Make sure the user is authenticated
        if (!$user = auth()->user()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Find the invoice by ID
        $invoice = Invoices::where('id', $id)->first();

        // Check if the invoice exists
        if (!$invoice) {
            return response()->json(['error' => 'Invoice not found'], 404);
        }

        // Check if the invoice belongs to the authenticated user
        if ($invoice->user_id !== $user->id && $invoice->client_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Get user info
        $user = User::where('id', $invoice->user_id)->first();

        // Get the invoice items
        $items = Invoices_Items::where('invoice_id', $invoice->id)->get();

        // Get the client
        $client = Clients::where('id', $invoice->client_id)->first();

        // Get the payments
        $payments = Payments::where('invoice_id', $invoice->id)->get();

        // Get the notifications for the invoice
        $notifications = $invoice->notifications()->get();

        // Return the invoice as a JSON response
        return response()->json(['invoice' => $invoice, 'user' => $user, 'items' => $items, 'client' => $client, 'payments' => $payments, 'notifications' => $notifications], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        // Validate the request data
        $validatedData = $request->validate([
            'clientId' => 'required|exists:clients,id',
            'invoiceNumber' => 'required|string|max:255',
            'invoiceDate' => 'required|date',
            'dueDate' => 'required|date',
            'items' => 'required|array',
            'subtotal' => 'required|numeric',
            'tax' => 'required|numeric',
            'taxRate' => 'required|numeric',
            'total' => 'required|numeric',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
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

        // Find the invoice by ID
        $invoice = Invoices::where('user_id', $request->user()->id)->where('id', $id)->first();

        // Check if the invoice exists
        if (!$invoice) {
            return response()->json(['error' => 'Invoice not found'], 404);
        }

        // Check if the invoice belongs to the authenticated user
        if ($invoice->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Update the invoice
        $invoice->client_id = $validatedData['clientId'];
        $invoice->invoice_number = $validatedData['invoiceNumber'];
        $invoice->issue_date = $validatedData['invoiceDate'];
        $invoice->due_date = $validatedData['dueDate'];
        $invoice->sub_total = $validatedData['subtotal'];
        $invoice->tax_rate = $validatedData['taxRate'];
        $invoice->total_amount = $validatedData['total'];
        $invoice->tax_amount = $validatedData['tax'];
        $invoice->notes = $request->input('notes', null);
        $invoice->terms = $request->input('terms', null);
        $invoice->status = 'pending'; // Default status

        // Save the invoice
        $invoice->save();

        // Loop through the items and add them to the invoice
        // First, delete existing items
        Invoices_Items::where('invoice_id', $invoice->id)->delete();

        // Then, add new items
        foreach ($validatedData['items'] as $item) {
            $invoiceItem = new Invoices_Items();
            $invoiceItem->invoice_id = $invoice->id;
            $invoiceItem->item = $item['item'];
            $invoiceItem->description = $item['description'];
            $invoiceItem->quantity = $item['quantity'];
            $invoiceItem->unit_price = $item['unit_price'];

            // Save the invoice item
            $invoiceItem->save();
        }

        // If client is updated, send email to the client
        if ($invoice->client_id !== $validatedData['clientId']) {
            Mail::to($client->email)->send(new InvoiceCreatedMail($invoice, $client));
        }

        // Notify the client that the invoice has been updated
        $client->notify(new InvoiceUpdatedNotification($invoice, $client));

        // Add invoice update activity
        Invoice_Activity::create([
            'invoice_id' => $invoice->id,
            'action' => 'Invoice updated',
            'description' => 'Invoice updated by user',
            'user_id' => $request->user()->id,
        ]);

        // Return the updated invoice
        return response()->json(['message' => 'Invoice updated successfully', 'invoice' => $invoice], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Make sure the user is authenticated
        if (!$user = auth()->user()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Find the invoice by ID
        $invoice = Invoices::where('user_id', $user->id)->where('id', $id)->first();

        // Check if the invoice exists
        if (!$invoice) {
            return response()->json(['error' => 'Invoice not found'], 404);
        }

        // Check if the invoice belongs to the authenticated user
        if ($invoice->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Delete the invoice
        $invoice->delete();

        // Delete the invoice items
        Invoices_Items::where('invoice_id', $invoice->id)->delete();

        // Delete the invoice payments
        Payments::where('invoice_id', $invoice->id)->delete();

        // Delete the invoice notifications
        Invoice_Activity::where('invoice_id', $invoice->id)->delete();

        // Return success response
        return response()->json(['message' => 'Invoice deleted successfully'], 200);
    }
}
