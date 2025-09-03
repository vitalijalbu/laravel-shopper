<?php

namespace Shopper\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AbandonedCart extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'customer_id',
        'email',
        'cart_data',
        'total_amount',
        'currency_code',
        'abandoned_at',
        'recovered_at',
        'recovered_order_id',
        'recovery_emails_sent',
        'last_recovery_email_sent_at',
        'metadata',
    ];

    protected $casts = [
        'cart_data' => 'array',
        'total_amount' => 'decimal:2',
        'abandoned_at' => 'datetime',
        'recovered_at' => 'datetime',
        'last_recovery_email_sent_at' => 'datetime',
        'recovery_emails_sent' => 'integer',
        'metadata' => 'array',
    ];

    // Relationships
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function recoveredOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'recovered_order_id');
    }

    // Scopes
    public function scopeRecovered($query)
    {
        return $query->whereNotNull('recovered_at');
    }

    public function scopeNotRecovered($query)
    {
        return $query->whereNull('recovered_at');
    }

    public function scopeEligibleForRecovery($query)
    {
        return $query->whereNull('recovered_at')
            ->where('abandoned_at', '>=', now()->subDays(30)); // 30 days limit
    }

    public function scopeRecentlyAbandoned($query, int $hours = 24)
    {
        return $query->where('abandoned_at', '>=', now()->subHours($hours));
    }

    // Accessors
    public function getItemsCountAttribute(): int
    {
        return count($this->cart_data['items'] ?? []);
    }

    public function getCustomerNameAttribute(): ?string
    {
        return $this->customer?->name ??
               ($this->cart_data['customer']['name'] ?? null);
    }

    public function getDaysAbandonedAttribute(): int
    {
        return $this->abandoned_at->diffInDays(now());
    }

    public function getIsRecoveredAttribute(): bool
    {
        return ! is_null($this->recovered_at);
    }

    // Methods
    public function markAsRecovered(int $orderId): bool
    {
        return $this->update([
            'recovered_at' => now(),
            'recovered_order_id' => $orderId,
        ]);
    }

    public function incrementRecoveryEmails(): bool
    {
        return $this->update([
            'recovery_emails_sent' => $this->recovery_emails_sent + 1,
            'last_recovery_email_sent_at' => now(),
        ]);
    }

    public function canSendRecoveryEmail(): bool
    {
        // Don't send if already recovered
        if ($this->is_recovered) {
            return false;
        }

        // Don't send if too old (30 days)
        if ($this->abandoned_at->diffInDays(now()) > 30) {
            return false;
        }

        // Don't send if too many emails already sent
        if ($this->recovery_emails_sent >= 3) {
            return false;
        }

        // Don't send if last email was sent less than 24 hours ago
        if ($this->last_recovery_email_sent_at &&
            $this->last_recovery_email_sent_at->diffInHours(now()) < 24) {
            return false;
        }

        return true;
    }

    public function getRecoveryUrl(): string
    {
        return route('cart.recover', [
            'token' => encrypt([
                'cart_id' => $this->id,
                'email' => $this->email,
            ]),
        ]);
    }
}
