<?php

namespace ReavaPay\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use ReavaPay\Models\ReavaPaySetting;
use ReavaPay\Models\ReavaPayTransaction;
use ReavaPay\ReavaPayManager;

class AdminController extends Controller
{
    /**
     * Settings page — shows connect button or credentials.
     */
    public function settings()
    {
        $settings = ReavaPaySetting::instance();
        $credentials = $this->getCredentialsForDisplay($settings);

        // Get float balance if connected
        $floatBalance = null;
        if ($settings->is_verified && $settings->hasValidCredentials()) {
            try {
                $manager = new ReavaPayManager($settings->api_secret, $settings->base_url);
                $accounts = $manager->floatAccounts()->list();
                $floatBalance = $accounts->data[0] ?? null;
            } catch (\Throwable $e) {
                Log::debug('Could not fetch float balance: ' . $e->getMessage());
            }
        }

        return view('reava-pay::admin.settings', compact('settings', 'credentials', 'floatBalance'));
    }

    /**
     * Connect to Reava Pay — registers as a merchant.
     */
    public function connect(Request $request)
    {
        $request->validate([
            'business_name' => 'required|string|max:255',
            'email' => 'required|email|max:191',
            'phone' => 'nullable|string|max:20',
        ]);

        $settings = ReavaPaySetting::instance();

        // Don't re-register if already connected
        if ($settings->is_verified) {
            return back()->with('info', 'Already connected to Reava Pay.');
        }

        $baseUrl = $settings->base_url ?: config('reava-pay.base_url', 'https://reavapay.com/api/v1');

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->timeout(30)->post(rtrim($baseUrl, '/') . '/merchants/register', [
                'business_name' => $request->business_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'source' => config('app.name', 'laravel'),
            ]);

            if (!$response->successful()) {
                $body = $response->json();
                Log::error('Reava Pay registration failed', ['status' => $response->status(), 'response' => $body]);
                return back()->with('error', 'Registration failed: ' . ($body['message'] ?? 'Unknown error'));
            }

            $result = $response->json();
            $data = $result['data'] ?? [];

            $settings->update([
                'api_key' => $data['credentials']['key_id'] ?? null,
                'public_key' => $data['credentials']['key_id'] ?? null,
                'webhook_secret' => $data['webhook']['secret'] ?? ('whsec_' . Str::random(24)),
                'base_url' => $baseUrl,
                'environment' => $data['credentials']['environment'] ?? 'production',
                'is_active' => true,
                'is_verified' => true,
                'verified_at' => now(),
                'last_synced_at' => now(),
                'metadata' => [
                    'merchant_id' => $data['merchant_id'] ?? null,
                    'business_name' => $data['business_name'] ?? $request->business_name,
                    'float_account' => $data['float_account']['account_number'] ?? null,
                    'login_email' => $data['email'] ?? $request->email,
                    'login_password' => $data['login_password'] ?? null,
                    'onboarded_at' => now()->toIso8601String(),
                ],
            ]);

            // Store encrypted API secret
            if ($data['credentials']['secret_key'] ?? null) {
                $settings->api_secret = $data['credentials']['secret_key'];
                $settings->save();
            }

            return back()->with('success', 'Successfully connected to Reava Pay!');
        } catch (\Throwable $e) {
            Log::error('Reava Pay connection error: ' . $e->getMessage());
            return back()->with('error', 'Connection failed: ' . $e->getMessage());
        }
    }

    /**
     * Disconnect — deletes settings for a fresh reconnect.
     */
    public function disconnect()
    {
        $settings = ReavaPaySetting::first();
        if ($settings) {
            $settings->delete();
        }

        return redirect()->route('reava-pay.admin.settings')
            ->with('success', 'Disconnected from Reava Pay. You can reconnect anytime.');
    }

    /**
     * Update settings (channels, currency).
     */
    public function update(Request $request)
    {
        $settings = ReavaPaySetting::instance();

        $settings->update($request->only([
            'mpesa_enabled', 'card_enabled', 'bank_transfer_enabled', 'default_currency',
        ]));

        return back()->with('success', 'Settings updated.');
    }

    /**
     * Test API connection.
     */
    public function testConnection()
    {
        $settings = ReavaPaySetting::instance();

        if (!$settings->hasValidCredentials()) {
            return back()->with('error', 'No API credentials configured.');
        }

        try {
            $manager = new ReavaPayManager($settings->api_secret, $settings->base_url);
            $me = $manager->me();

            return back()->with('success', 'Connection successful! Merchant: ' . ($me->data->business_name ?? $me->data->name ?? 'OK'));
        } catch (\Throwable $e) {
            return back()->with('error', 'Connection failed: ' . $e->getMessage());
        }
    }

    /**
     * Transactions list.
     */
    public function transactions(Request $request)
    {
        $query = ReavaPayTransaction::latest();

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        if ($request->filled('channel') && $request->channel !== 'all') {
            $query->where('channel', $request->channel);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('local_reference', 'like', "%{$s}%")
                  ->orWhere('reava_reference', 'like', "%{$s}%")
                  ->orWhere('phone', 'like', "%{$s}%");
            });
        }

        $transactions = $query->paginate(20)->withQueryString();

        $stats = [
            'total_volume' => ReavaPayTransaction::completed()->sum('amount'),
            'this_month' => ReavaPayTransaction::completed()->where('created_at', '>=', now()->startOfMonth())->sum('amount'),
            'total_count' => ReavaPayTransaction::count(),
            'success_rate' => $this->calculateSuccessRate(),
        ];

        return view('reava-pay::admin.transactions', compact('transactions', 'stats'));
    }

    /**
     * Transaction detail.
     */
    public function transactionDetail($id)
    {
        $transaction = ReavaPayTransaction::findOrFail($id);

        return view('reava-pay::admin.transaction-detail', compact('transaction'));
    }

    /**
     * Sync a transaction status from Reava Pay API.
     */
    public function syncTransaction($id)
    {
        $transaction = ReavaPayTransaction::findOrFail($id);
        $settings = ReavaPaySetting::instance();

        if (!$settings->hasValidCredentials() || !$transaction->reava_reference) {
            return back()->with('error', 'Cannot sync — missing credentials or reference.');
        }

        try {
            $manager = new ReavaPayManager($settings->api_secret, $settings->base_url);
            $result = $manager->collections()->get($transaction->reava_reference);

            $apiStatus = $result->data->status ?? null;

            if ($apiStatus === 'completed' && !$transaction->isCompleted()) {
                $transaction->markAsCompleted([
                    'provider_reference' => $result->data->provider_reference ?? $transaction->provider_reference,
                    'reava_response' => (array) $result->data,
                ]);
                return back()->with('success', 'Transaction synced — marked as completed.');
            } elseif ($apiStatus === 'failed' && !$transaction->isFailed()) {
                $transaction->markAsFailed($result->data->failure_reason ?? 'Payment failed', [
                    'reava_response' => (array) $result->data,
                ]);
                return back()->with('success', 'Transaction synced — marked as failed.');
            }

            return back()->with('info', "Status on Reava Pay: {$apiStatus}. No change needed.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Sync failed: ' . $e->getMessage());
        }
    }

    // ─── Helpers ──────────────────────────────────────────

    /**
     * Initiate an M-Pesa STK push payment for an invoice.
     */
    public function collectPayment(Request $request)
    {
        $request->validate([
            'invoice_id' => 'required|integer',
            'phone' => 'required|string',
            'amount' => 'required|numeric|min:1',
        ]);

        $settings = ReavaPaySetting::instance();

        if (!$settings->hasValidCredentials()) {
            return back()->with('rp_error', 'Reava Pay is not connected. Go to Settings > Reava Pay to connect.');
        }

        // Normalize phone
        $phone = preg_replace('/[^0-9]/', '', $request->phone);
        if (strlen($phone) === 9) $phone = '254' . $phone;
        elseif (strlen($phone) === 10 && $phone[0] === '0') $phone = '254' . substr($phone, 1);

        $reference = 'INV-PAY-' . $request->invoice_id . '-' . date('YmdHis') . '-' . strtoupper(substr(uniqid(), -4));

        // Create local transaction record
        $txn = ReavaPayTransaction::create([
            'payer_type' => null,
            'payer_id' => null,
            'type' => ReavaPayTransaction::TYPE_INVOICE,
            'channel' => 'mpesa',
            'amount' => $request->amount,
            'charge_amount' => 0,
            'net_amount' => $request->amount,
            'currency' => 'KES',
            'status' => ReavaPayTransaction::STATUS_PENDING,
            'local_reference' => $reference,
            'phone' => $phone,
            'account_reference' => $reference,
            'description' => 'Invoice payment: INV #' . $request->invoice_id,
            'payable_type' => 'App\\Models\\Invoice',
            'payable_id' => $request->invoice_id,
            'callback_url' => url(config('reava-pay.webhook_path', 'webhooks/reava-pay')),
            'initiated_at' => now(),
            'metadata' => [
                'invoice_id' => $request->invoice_id,
                'initiated_by' => auth()->id(),
            ],
        ]);

        try {
            $manager = new ReavaPayManager($settings->api_secret, $settings->base_url);
            $result = $manager->collections()->create([
                'amount' => $request->amount,
                'currency' => 'KES',
                'channel' => 'mpesa',
                'phone' => $phone,
                'account_reference' => $reference,
                'description' => 'NPV Invoice Payment',
                'callback_url' => url(config('reava-pay.webhook_path', 'webhooks/reava-pay')),
                'metadata' => [
                    'local_reference' => $reference,
                    'invoice_id' => $request->invoice_id,
                    'source' => config('app.name', 'NPV'),
                ],
            ]);

            $txn->update([
                'status' => ReavaPayTransaction::STATUS_PROCESSING,
                'reava_reference' => $result->data->reference ?? null,
                'reava_response' => (array) $result->data,
            ]);

            return back()->with('rp_success', 'M-Pesa payment request sent to ' . $phone . '. The member will receive an STK push prompt on their phone.');
        } catch (\Throwable $e) {
            $txn->markAsFailed($e->getMessage());
            Log::error('Reava Pay collection failed', ['error' => $e->getMessage(), 'invoice_id' => $request->invoice_id]);
            return back()->with('rp_error', 'Payment request failed: ' . $e->getMessage());
        }
    }

    private function getCredentialsForDisplay(ReavaPaySetting $settings): ?array
    {
        if (!$settings->exists || !$settings->is_verified) {
            return null;
        }

        $meta = $settings->metadata ?? [];

        return [
            'api_key' => $settings->api_key,
            'api_secret' => $settings->api_secret,
            'merchant_id' => $meta['merchant_id'] ?? null,
            'business_name' => $meta['business_name'] ?? null,
            'float_account' => $meta['float_account'] ?? null,
            'login_email' => $meta['login_email'] ?? null,
            'login_password' => $meta['login_password'] ?? null,
            'environment' => $settings->environment,
            'is_active' => $settings->is_active,
            'is_verified' => $settings->is_verified,
            'connected_at' => $meta['onboarded_at'] ?? null,
            'webhook_url' => url(config('reava-pay.webhook_path', 'webhooks/reava-pay')),
        ];
    }

    private function calculateSuccessRate(): float
    {
        $total = ReavaPayTransaction::whereIn('status', ['completed', 'failed'])->count();
        if ($total === 0) return 0;
        return round((ReavaPayTransaction::completed()->count() / $total) * 100, 1);
    }
}
