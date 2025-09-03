<?php

namespace Shopper\Data\AbandonedCart;

use Spatie\LaravelData\Data;

class AbandonedCartData extends Data
{
    public function __construct(
        public ?int $id,
        public int $customer_id,
        public string $session_id,
        public array $cart_data,
        public string $status,
        public ?string $recovery_email_sent_at,
        public ?string $recovered_at,
        public ?string $notes,
        public ?string $created_at,
        public ?string $updated_at,
    ) {}

    public static function fromModel(\Shopper\Models\AbandonedCart $cart): self
    {
        return new self(
            id: $cart->id,
            customer_id: $cart->customer_id,
            session_id: $cart->session_id,
            cart_data: $cart->cart_data ?? [],
            status: $cart->status,
            recovery_email_sent_at: $cart->recovery_email_sent_at?->toISOString(),
            recovered_at: $cart->recovered_at?->toISOString(),
            notes: $cart->notes,
            created_at: $cart->created_at?->toISOString(),
            updated_at: $cart->updated_at?->toISOString(),
        );
    }
}
