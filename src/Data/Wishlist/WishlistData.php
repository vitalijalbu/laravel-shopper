<?php

namespace Shopper\Data\Wishlist;

use Shopper\Models\Wishlist;
use Spatie\LaravelData\Data;

class WishlistData extends Data
{
    public function __construct(
        public int $id,
        public int $customer_id,
        public string $name,
        public ?string $description,
        public string $status,
        public bool $is_shared,
        public ?string $share_token,
        public int $items_count,
        public string $created_at,
        public string $updated_at,
        public ?array $customer = null,
        public ?array $items = null,
    ) {}

    public static function fromModel(Wishlist $wishlist): self
    {
        return new self(
            id: $wishlist->id,
            customer_id: $wishlist->customer_id,
            name: $wishlist->name,
            description: $wishlist->description,
            status: $wishlist->status,
            is_shared: $wishlist->is_shared,
            share_token: $wishlist->share_token,
            items_count: $wishlist->items_count ?? $wishlist->items()->count(),
            created_at: $wishlist->created_at->toISOString(),
            updated_at: $wishlist->updated_at->toISOString(),
            customer: $wishlist->relationLoaded('customer') && $wishlist->customer ? [
                'id' => $wishlist->customer->id,
                'first_name' => $wishlist->customer->first_name,
                'last_name' => $wishlist->customer->last_name,
                'email' => $wishlist->customer->email,
            ] : null,
            items: $wishlist->relationLoaded('items') ? 
                $wishlist->items->map(fn($item) => [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'notes' => $item->notes,
                    'created_at' => $item->created_at->toISOString(),
                    'product' => $item->relationLoaded('product') && $item->product ? [
                        'id' => $item->product->id,
                        'name' => $item->product->name,
                        'price' => $item->product->price,
                        'image' => $item->product->image,
                    ] : null,
                ])->toArray() : null,
        );
    }
}
