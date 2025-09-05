<?php

namespace Shopper\Data\Cart;

use Shopper\Enums\CartStatus;
use Shopper\Models\Cart;
use Spatie\LaravelData\Data;

class CartData extends Data
{
    public function __construct(
        public int $id,
        public ?string $session_id,
        public ?int $customer_id,
        public ?string $email,
        public CartStatus $status,
        public ?array $items,
        public float $subtotal,
        public float $tax_amount,
        public float $shipping_amount,
        public float $discount_amount,
        public float $total_amount,
        public string $currency,
        public ?string $last_activity_at,
        public ?string $abandoned_at,
        public int $recovery_emails_sent,
        public ?string $last_recovery_email_sent_at,
        public bool $recovered,
        public ?string $recovered_at,
        public ?int $converted_order_id,
        public ?array $shipping_address,
        public ?array $billing_address,
        public ?array $metadata,
        public string $created_at,
        public string $updated_at,
        public ?array $customer = null,
        public ?int $items_count = null,
        public ?int $age_in_hours = null,
    ) {}

    public static function fromModel(Cart $cart): self
    {
        return new self(
            id: $cart->id,
            session_id: $cart->session_id,
            customer_id: $cart->customer_id,
            email: $cart->email,
            status: $cart->status,
            items: $cart->items,
            subtotal: (float) $cart->subtotal,
            tax_amount: (float) $cart->tax_amount,
            shipping_amount: (float) $cart->shipping_amount,
            discount_amount: (float) $cart->discount_amount,
            total_amount: (float) $cart->total_amount,
            currency: $cart->currency,
            last_activity_at: $cart->last_activity_at?->toISOString(),
            abandoned_at: $cart->abandoned_at?->toISOString(),
            recovery_emails_sent: $cart->recovery_emails_sent,
            last_recovery_email_sent_at: $cart->last_recovery_email_sent_at?->toISOString(),
            recovered: $cart->recovered,
            recovered_at: $cart->recovered_at?->toISOString(),
            converted_order_id: $cart->converted_order_id,
            shipping_address: $cart->shipping_address,
            billing_address: $cart->billing_address,
            metadata: $cart->metadata,
            created_at: $cart->created_at->toISOString(),
            updated_at: $cart->updated_at->toISOString(),
            customer: $cart->relationLoaded('customer') && $cart->customer ? [
                'id' => $cart->customer->id,
                'first_name' => $cart->customer->first_name,
                'last_name' => $cart->customer->last_name,
                'email' => $cart->customer->email,
            ] : null,
            items_count: collect($cart->items ?? [])->sum('quantity'),
            age_in_hours: $cart->created_at->diffInHours(now()),
        );
    }
}
