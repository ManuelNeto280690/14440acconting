<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    /**
     * Display the specified invoice.
     */
    public function show(Invoice $invoice)
    {
        // Ensure the invoice belongs to the authenticated client
        if ($invoice->client_id !== Auth::id()) {
            abort(403, 'Unauthorized access to invoice');
        }

        return view('client.invoices.show', compact('invoice'));
    }

    /**
     * Download the specified invoice as PDF.
     */
    public function download(Invoice $invoice)
    {
        // Ensure the invoice belongs to the authenticated client
        if ($invoice->client_id !== Auth::id()) {
            abort(403, 'Unauthorized access to invoice');
        }

        try {
            // Load invoice with relationships
            $invoice->load(['client', 'items']);

            // Generate PDF
            $pdf = Pdf::loadView('client.invoices.pdf', compact('invoice'));
            
            // Log download activity
            activity()
                ->performedOn($invoice)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'download'])
                ->log('Invoice downloaded');

            return $pdf->download('invoice-' . $invoice->invoice_number . '.pdf');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to download invoice: ' . $e->getMessage());
        }
    }

    /**
     * Process payment for the specified invoice.
     */
    public function pay(Request $request, Invoice $invoice)
    {
        // Ensure the invoice belongs to the authenticated client
        if ($invoice->client_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to invoice'
            ], 403);
        }

        // Check if invoice is already paid
        if ($invoice->status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Invoice is already paid'
            ], 400);
        }

        // Check if invoice is overdue
        if ($invoice->due_date < now() && $invoice->status !== 'paid') {
            $invoice->update(['status' => 'overdue']);
        }

        $request->validate([
            'payment_method' => 'required|string|in:credit_card,bank_transfer,paypal,stripe',
            'payment_reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            // In a real implementation, you would integrate with payment gateways
            // For now, we'll simulate payment processing
            
            $paymentData = [
                'payment_method' => $request->payment_method,
                'payment_reference' => $request->payment_reference ?: 'PAY-' . strtoupper(uniqid()),
                'paid_at' => now(),
                'paid_amount' => $invoice->total_amount,
                'notes' => $request->notes,
                'payment_gateway_response' => [
                    'status' => 'success',
                    'transaction_id' => 'TXN-' . strtoupper(uniqid()),
                    'processed_at' => now()->toISOString(),
                ]
            ];

            // Update invoice
            $invoice->update([
                'status' => 'paid',
                'paid_at' => now(),
                'paid_amount' => $invoice->total_amount,
                'payment_method' => $request->payment_method,
                'payment_reference' => $paymentData['payment_reference'],
                'payment_notes' => $request->notes,
                'meta' => array_merge($invoice->meta ?? [], [
                    'payment_data' => $paymentData
                ])
            ]);

            // Log payment activity
            activity()
                ->performedOn($invoice)
                ->causedBy(Auth::user())
                ->withProperties([
                    'action' => 'payment',
                    'amount' => $invoice->total_amount,
                    'method' => $request->payment_method
                ])
                ->log('Invoice payment processed');

            // Send payment confirmation (in real app, this would be a job)
            $this->sendPaymentConfirmation($invoice);

            return response()->json([
                'success' => true,
                'message' => 'Payment processed successfully',
                'invoice' => $invoice->fresh()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Payment processing failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send payment confirmation (placeholder for real implementation).
     */
    private function sendPaymentConfirmation(Invoice $invoice): void
    {
        // In a real implementation, this would dispatch a job to send email
        // dispatch(new SendPaymentConfirmationJob($invoice));
        
        // For now, we'll just log it
        \Log::info('Payment confirmation sent for invoice: ' . $invoice->invoice_number);
    }
}