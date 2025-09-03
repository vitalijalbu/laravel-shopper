<?php

namespace Shopper\Data\StockNotification;

use Spatie\LaravelData\Data;

class StockNotificationData extends Data
{
    public function __construct(
        public ?int $id,
        public int $customer_id,
        public string $product_type,
        public int $product_id,
        public ?string $product_handle,
        public array $product_data,
        public array $variant_data,
        public string $email,
        public ?string $phone,
        public string $preferred_method,
        public string $status,
        public ?string $notified_at,
        public ?string $created_at,
        public ?string $updated_at,
    ) {}

    public static function fromModel(\Shopper\Models\StockNotification $notification): self
    {
        return new self(
            id: $notification->id,
            customer_id: $notification->customer_id,
            product_type: $notification->product_type,
            product_id: $notification->product_id,
            product_handle: $notification->product_handle,
            product_data: $notification->product_data ?? [],
            variant_data: $notification->variant_data ?? [],
            email: $notification->email,
            phone: $notification->phone,
            preferred_method: $notification->preferred_method,
            status: $notification->status,
            notified_at: $notification->notified_at?->toISOString(),
            created_at: $notification->created_at?->toISOString(),
            updated_at: $notification->updated_at?->toISOString(),
        );
    }
}
