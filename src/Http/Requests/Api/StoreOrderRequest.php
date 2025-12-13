<?php

declare(strict_types=1);

namespace Cartino\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', \Cartino\Models\Order::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['nullable', 'exists:customers,id'],
            'customer_email' => ['required', 'email'],
            'customer_details' => ['required', 'array'],
            'currency_id' => ['required', 'exists:currencies,id'],
            'site_id' => ['nullable', 'exists:sites,id'],
            'subtotal' => ['required', 'numeric', 'min:0'],
            'tax_total' => ['required', 'numeric', 'min:0'],
            'shipping_total' => ['required', 'numeric', 'min:0'],
            'total' => ['required', 'numeric', 'min:0'],
            'shipping_address' => ['required', 'array'],
            'billing_address' => ['required', 'array'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.product_id' => ['required', 'exists:products,id'],
            'lines.*.quantity' => ['required', 'integer', 'min:1'],
            'lines.*.unit_price' => ['required', 'numeric', 'min:0'],
        ];
    }
}
