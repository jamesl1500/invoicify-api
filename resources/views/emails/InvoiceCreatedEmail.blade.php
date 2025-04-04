<!DOCTYPE html>
<html>
<head>
    <title>New Invoice from {{ config('app.name') }}</title>
</head>
<body>
    <h2>Hello, {{ $client->name }}</h2>
    <p>You have a new invoice from {{ config('app.name') }}.</p>

    <p><strong>Invoice Number:</strong> {{ $invoice->invoice_number }}</p>
    <p><strong>Amount Due:</strong> ${{ number_format($invoice->total_amount, 2) }}</p>
    <p><strong>Due Date:</strong> {{ $invoice->due_date }}</p>

    <p><a href="{{ url('/invoices/view/' . $invoice->id) }}">View Invoice</a></p>

    <p>Thank you for your business!</p>
</body>
</html>