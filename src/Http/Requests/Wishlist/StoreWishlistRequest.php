<?php

namespace Shopper\Http\Requests\Wishlist;

use Illuminate\Foundation\Http\FormRequest;
use Shopper\Enums\WishlistStatus;
use Illuminate\Validation\Rule;

class StoreWishlistRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => 'required|exists:customers,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'status' => ['nullable', Rule::enum(WishlistStatus::class)],
            'is_shared' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'customer_id.required' => __('shopper::validation.wishlist.customer_id.required'),
            'customer_id.exists' => __('shopper::validation.wishlist.customer_id.exists'),
            'name.required' => __('shopper::validation.wishlist.name.required'),
            'name.string' => __('shopper::validation.wishlist.name.string'),
            'name.max' => __('shopper::validation.wishlist.name.max'),
            'description.string' => __('shopper::validation.wishlist.description.string'),
            'description.max' => __('shopper::validation.wishlist.description.max'),
            'status.enum' => __('shopper::validation.wishlist.status.enum'),
            'is_shared.boolean' => __('shopper::validation.wishlist.is_shared.boolean'),
        ];
    }
}
