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

            match ($action) {
                'completed' => $transaction->markAsCompleted([
                    'provider_reference' => $eventData['provider_reference'] ?? $transaction->provider_reference,
                    'reava_response' => $eventData,
                ]),
                'failed' => $transaction->markAsFailed(
                    $eventData['failure_reason'] ?? $eventData['message'] ?? 'Payment failed',
                    ['reava_response' => $eventData]
                ),
                'reversed' => $transaction->update([
                    'status' => ReavaPayTransaction::STATUS_REVERSED,
                    'reava_response' => $eventData,
                ]),
                default => null,
            };

            return response()->json(['success' => true, 'message' => 'Transaction updated']);
        }

        return response()->json(['success' => true, 'message' => 'Event acknowledged']);
    }
}
