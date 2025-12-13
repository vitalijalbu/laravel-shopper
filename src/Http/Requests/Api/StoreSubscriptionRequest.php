<?php

declare(strict_types=1);

namespace Cartino\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'product_variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'billing_interval' => ['required', 'string', 'in:day,week,month,year'],
            'billing_interval_count' => ['required', 'integer', 'min:1', 'max:365'],
            'price' => ['required', 'numeric', 'min:0'],
            'currency_id' => ['required', 'integer', 'exists:currencies,id'],
            'trial_end_at' => ['nullable', 'date', 'after:now'],
            'payment_method' => ['nullable', 'string', 'max:255'],
            'payment_details' => ['nullable', 'array'],
            'metadata' => ['nullable', 'array'],
            'notes' => ['nullable', 'string'],
            'data' => ['nullable', 'array'],
        ];
    }
}
