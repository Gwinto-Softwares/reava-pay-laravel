<?php

namespace ReavaPay\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use ReavaPay\Events\WebhookReceived;
use ReavaPay\Models\ReavaPaySetting;
use ReavaPay\Models\ReavaPayTransaction;

class WebhookController extends Controller
{
    public function handle(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $signature = $request->header('X-Reava-Signature', '');
        $data = json_decode($payload, true);

        if (!$data) {
            return response()->json(['error' => 'Invalid JSON payload'], 400);
        }

        // Verify signature against config or DB secret
        if ($signature) {
            $secrets = array_filter([
                config('reava-pay.webhook_secret'),
                ReavaPaySetting::first()?->webhook_secret,
            ]);

            $verified = false;
            foreach ($secrets as $secret) {
                if (hash_equals(hash_hmac('sha256', $payload, $secret), $signature)) {
                    $verified = true;
                    break;
                }
            }

            if (!$verified && !empty($secrets)) {
                return response()->json(['error' => 'Invalid signature'], 403);
            }
        }

        $event = $data['event'] ?? 'unknown';
        $eventData = $data['data'] ?? [];

        Log::info('Reava Pay Webhook', ['event' => $event]);

        // Dispatch the generic event for custom listeners
        WebhookReceived::dispatch($data, $event);

        // Find and update the local transaction
        $refs = array_filter([
            $eventData['reference'] ?? null,
            $eventData['external_reference'] ?? null,
            $eventData['transaction_id'] ?? null,
        ]);

        $transaction = !empty($refs)
            ? ReavaPayTransaction::whereIn('reava_reference', $refs)->first()
            : null;

        if ($transaction) {
            $transaction->update(['webhook_payload' => $data]);

            $action = null;
            if (str_ends_with($event, '.completed')) $action = 'completed';
            elseif (str_ends_with($event, '.failed')) $action = 'failed';
            elseif (str_ends_with($event, '.reversed')) $action = 'reversed';

            if ($action === 'completed') {
                $transaction->markAsCompleted([
                    'provider_reference' => $eventData['provider_reference'] ?? $transaction->provider_reference,
                    'reava_response' => $eventData,
                ]);

                // Auto-process linked payable (Invoice → Receipt)
                $this->processPayableCompletion($transaction, $eventData);
            } elseif ($action === 'failed') {
                $transaction->markAsFailed(
                    $eventData['failure_reason'] ?? $eventData['message'] ?? 'Payment failed',
                    ['reava_response' => $eventData]
                );
            } elseif ($action === 'reversed') {
                $transaction->update([
                    'status' => ReavaPayTransaction::STATUS_REVERSED,
                    'reava_response' => $eventData,
                ]);
            }

            return response()->json(['success' => true, 'message' => 'Transaction updated']);
        }

        return response()->json(['success' => true, 'message' => 'Event acknowledged']);
    }

    /**
     * Auto-process linked payable when payment completes.
     * For Invoice: creates a Receipt and updates invoice status.
     * Works with any app that has Invoice/Receipt models (NPV, etc).
     */
    private function processPayableCompletion(ReavaPayTransaction $transaction, array $eventData): void
    {
        if (!$transaction->payable_type || !$transaction->payable_id) {
            return;
        }

        try {
            // Handle Invoice payable → auto-create Receipt
            if (str_ends_with($transaction->payable_type, 'Invoice')) {
                $invoiceClass = $transaction->payable_type;

                if (!class_exists($invoiceClass)) {
                    return;
                }

                $invoice = $invoiceClass::find($transaction->payable_id);

                if (!$invoice) {
                    return;
                }

                // Try to create a Receipt (NPV pattern)
                $receiptClass = str_replace('Invoice', 'Receipt', $invoiceClass);

                if (class_exists($receiptClass)) {
                    // Generate receipt number
                    $lastReceipt = $receiptClass::orderByDesc('id')->first();
                    $nextNum = $lastReceipt ? ((int) substr($lastReceipt->receipt_number, -4)) + 1 : 1;
                    $receiptNumber = 'RCT-' . date('Ym') . '-' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

                    $receiptClass::create([
                        'receipt_number' => $receiptNumber,
                        'invoice_id' => $invoice->id,
                        'registration_id' => $invoice->registration_id ?? null,
                        'receipt_date' => now(),
                        'amount_paid' => $transaction->amount,
                        'payment_method' => 'mpesa',
                        'transaction_reference' => $transaction->reava_reference,
                        'mpesa_receipt_number' => $eventData['provider_reference'] ?? $transaction->provider_reference,
                        'payment_notes' => 'Paid via Reava Pay - ' . $transaction->local_reference,
                    ]);

                    // Update invoice status if fully paid
                    $totalPaid = $receiptClass::where('invoice_id', $invoice->id)->sum('amount_paid');
                    if ($totalPaid >= $invoice->total_amount) {
                        $invoice->update(['status' => 'paid', 'paid_at' => now()]);
                    }

                    Log::info('Reava Pay: Auto-created receipt for invoice', [
                        'invoice_id' => $invoice->id,
                        'receipt_number' => $receiptNumber,
                        'amount' => $transaction->amount,
                    ]);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Reava Pay: Failed to process payable completion', [
                'payable_type' => $transaction->payable_type,
                'payable_id' => $transaction->payable_id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
