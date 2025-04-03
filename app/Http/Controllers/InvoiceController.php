<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Invoices;
use App\Models\Clients;
use App\Models\Payments;
use App\Models\User;
use App\Models\Invoices_Items;

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
        $invoice->invoice_date = $validatedData['invoiceDate'];
        $invoice->due_date = $validatedData['dueDate'];
        $invoice->sub_total = $validatedData['subtotal'];
        $invoice->tax_rate = $validatedData['taxRate'];
        $invoice->tax_amount = $validatedData['tax'];
        $invoice->total_amount = $validatedData['total'];
        $invoice->status = 'draft'; // Default status

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

        // Return the created invoice
        return response()->json(['message' => 'Invoice created successfully', 'invoice' => $invoice], 201);
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
