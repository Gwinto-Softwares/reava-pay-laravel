<?php

namespace ReavaPay\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class ReavaPaySetting extends Model
{
    protected $table = 'reava_pay_settings';

    protected $fillable = [
        'api_key', 'public_key', 'api_secret_encrypted', 'webhook_secret',
        'base_url', 'environment', 'default_currency',
        'mpesa_enabled', 'card_enabled', 'bank_transfer_enabled',
        'is_active', 'is_verified', 'verified_at', 'last_synced_at', 'metadata',
    ];

    protected function casts(): array
    {
        return [
            'mpesa_enabled' => 'boolean',
            'card_enabled' => 'boolean',
            'bank_transfer_enabled' => 'boolean',
            'is_active' => 'boolean',
            'is_verified' => 'boolean',
            'verified_at' => 'datetime',
            'last_synced_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    // ─── Encrypted API Secret ─────────────────────────────

    public function getApiSecretAttribute(): ?string
    {
        if (!$this->api_secret_encrypted) {
            return null;
        }

        try {
            return Crypt::decryptString($this->api_secret_encrypted);
        } catch (\Throwable) {
            return null;
        }
    }

    public function setApiSecretAttribute(?string $value): void
    {
        $this->attributes['api_secret_encrypted'] = $value ? Crypt::encryptString($value) : null;
    }

    // ─── Helpers ──────────────────────────────────────────

    public function hasValidCredentials(): bool
    {
        return !empty($this->api_key) && !empty($this->api_secret_encrypted);
    }

    public function getEnabledChannels(): array
    {
        $channels = [];
        if ($this->mpesa_enabled) $channels[] = 'mpesa';
        if ($this->card_enabled) $channels[] = 'card';
        if ($this->bank_transfer_enabled) $channels[] = 'bank_transfer';
        return $channels;
    }

    /**
     * Get the singleton settings record (or create one).
     */
    public static function instance(): self
    {
        return static::firstOrCreate([], [
            'base_url' => config('reava-pay.base_url', 'https://reavapay.com/api/v1'),
            'environment' => 'production',
        ]);
    }
}
