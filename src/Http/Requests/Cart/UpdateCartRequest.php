<?php

namespace Shopper\Http\Requests\Cart;

use Illuminate\Foundation\Http\FormRequest;
use Shopper\Enums\CartStatus;
use Illuminate\Validation\Rule;

class UpdateCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => 'sometimes|email|max:255',
            'status' => ['sometimes', Rule::enum(CartStatus::class)],
            'items' => 'sometimes|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1|max:999',
            'items.*.price' => 'required|numeric|min:0',
            'subtotal' => 'sometimes|numeric|min:0',
            'tax_amount' => 'sometimes|numeric|min:0',
            'shipping_amount' => 'sometimes|numeric|min:0',
            'discount_amount' => 'sometimes|numeric|min:0',
            'total_amount' => 'sometimes|numeric|min:0',
            'currency' => 'sometimes|string|size:3',
            'shipping_address' => 'sometimes|nullable|array',
            'billing_address' => 'sometimes|nullable|array',
            'metadata' => 'sometimes|nullable|array',
        ];
    }

    public function messages(): array
    {
        return [
            'email.email' => __('shopper::validation.cart.email.email'),
            'email.max' => __('shopper::validation.cart.email.max'),
            'status.enum' => __('shopper::validation.cart.status.enum'),
            'items.array' => __('shopper::validation.cart.items.array'),
            'items.*.product_id.required' => __('shopper::validation.cart.items.product_id.required'),
            'items.*.product_id.exists' => __('shopper::validation.cart.items.product_id.exists'),
            'items.*.quantity.required' => __('shopper::validation.cart.items.quantity.required'),
            'items.*.quantity.integer' => __('shopper::validation.cart.items.quantity.integer'),
            'items.*.quantity.min' => __('shopper::validation.cart.items.quantity.min'),
            'items.*.quantity.max' => __('shopper::validation.cart.items.quantity.max'),
            'items.*.price.required' => __('shopper::validation.cart.items.price.required'),
            'items.*.price.numeric' => __('shopper::validation.cart.items.price.numeric'),
            'items.*.price.min' => __('shopper::validation.cart.items.price.min'),
            'subtotal.numeric' => __('shopper::validation.cart.subtotal.numeric'),
            'subtotal.min' => __('shopper::validation.cart.subtotal.min'),
            'tax_amount.numeric' => __('shopper::validation.cart.tax_amount.numeric'),
            'tax_amount.min' => __('shopper::validation.cart.tax_amount.min'),
            'shipping_amount.numeric' => __('shopper::validation.cart.shipping_amount.numeric'),
            'shipping_amount.min' => __('shopper::validation.cart.shipping_amount.min'),
            'discount_amount.numeric' => __('shopper::validation.cart.discount_amount.numeric'),
            'discount_amount.min' => __('shopper::validation.cart.discount_amount.min'),
            'total_amount.numeric' => __('shopper::validation.cart.total_amount.numeric'),
            'total_amount.min' => __('shopper::validation.cart.total_amount.min'),
            'currency.string' => __('shopper::validation.cart.currency.string'),
            'currency.size' => __('shopper::validation.cart.currency.size'),
            'shipping_address.array' => __('shopper::validation.cart.shipping_address.array'),
            'billing_address.array' => __('shopper::validation.cart.billing_address.array'),
            'metadata.array' => __('shopper::validation.cart.metadata.array'),
        ];
    }
}
