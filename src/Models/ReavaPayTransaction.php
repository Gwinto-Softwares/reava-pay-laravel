<?php

namespace ReavaPay\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ReavaPayTransaction extends Model
{
    protected $table = 'reava_pay_transactions';

    const TYPE_COLLECTION = 'collection';
    const TYPE_PAYOUT = 'payout';
    const TYPE_MEMBERSHIP = 'membership';
    const TYPE_INVOICE = 'invoice';

    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_REVERSED = 'reversed';

    protected $fillable = [
        'uuid', 'payer_type', 'payer_id', 'type', 'channel',
        'amount', 'charge_amount', 'net_amount', 'currency', 'status',
        'reava_reference', 'provider_reference', 'local_reference',
        'phone', 'email', 'account_reference', 'description',
        'authorization_url', 'callback_url',
        'payable_type', 'payable_id',
        'reava_response', 'webhook_payload', 'metadata',
        'failure_reason', 'retry_count',
        'initiated_at', 'completed_at', 'failed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'charge_amount' => 'decimal:2',
            'net_amount' => 'decimal:2',
            'reava_response' => 'array',
            'webhook_payload' => 'array',
            'metadata' => 'array',
            'retry_count' => 'integer',
            'initiated_at' => 'datetime',
            'completed_at' => 'datetime',
            'failed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $txn) {
            if (empty($txn->uuid)) {
                $txn->uuid = (string) Str::uuid();
            }
        });
    }

    // ─── Relationships ────────────────────────────────────

    public function payer()
    {
        return $this->morphTo();
    }

    public function payable()
    {
        return $this->morphTo();
    }

    // ─── Status Helpers ───────────────────────────────────

    public function isPending(): bool { return $this->status === self::STATUS_PENDING; }
    public function isProcessing(): bool { return $this->status === self::STATUS_PROCESSING; }
    public function isCompleted(): bool { return $this->status === self::STATUS_COMPLETED; }
    public function isFailed(): bool { return $this->status === self::STATUS_FAILED; }

    public function markAsCompleted(array $extra = []): void
    {
        $this->update(array_merge([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => now(),
        ], $extra));
    }

    public function markAsFailed(?string $reason = null, array $extra = []): void
    {
        $this->update(array_merge([
            'status' => self::STATUS_FAILED,
            'failure_reason' => $reason,
            'failed_at' => now(),
        ], $extra));
    }

    // ─── Accessors ────────────────────────────────────────

    public function getFormattedAmountAttribute(): string
    {
        return $this->currency . ' ' . number_format($this->amount, 2);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_COMPLETED => 'success',
            self::STATUS_FAILED => 'danger',
            self::STATUS_PROCESSING, self::STATUS_PENDING => 'warning',
            self::STATUS_REVERSED => 'info',
            default => 'secondary',
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_COLLECTION => 'Collection',
            self::TYPE_PAYOUT => 'Payout',
            self::TYPE_MEMBERSHIP => 'Membership Payment',
            self::TYPE_INVOICE => 'Invoice Payment',
            default => ucfirst($this->type),
        };
    }

    public function getChannelLabelAttribute(): string
    {
        return match ($this->channel) {
            'mpesa' => 'M-Pesa',
            'card' => 'Card',
            'bank_transfer' => 'Bank Transfer',
            default => ucfirst($this->channel),
        };
    }

    // ─── Scopes ───────────────────────────────────────────

    public function scopePending($query) { return $query->where('status', self::STATUS_PENDING); }
    public function scopeProcessing($query) { return $query->where('status', self::STATUS_PROCESSING); }
    public function scopeCompleted($query) { return $query->where('status', self::STATUS_COMPLETED); }
    public function scopeFailed($query) { return $query->where('status', self::STATUS_FAILED); }
    public function scopeOfType($query, string $type) { return $query->where('type', $type); }
    public function scopeRecent($query, int $days = 30) { return $query->where('created_at', '>=', now()->subDays($days)); }
}
