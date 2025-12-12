<?php

namespace Cartino\Http\Requests\StockNotification;

use Illuminate\Foundation\Http\FormRequest;

class StoreStockNotificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => 'required|exists:customers,id',
            'product_type' => 'required|string|in:entry,collection,external',
            'product_id' => 'required|integer',
            'product_handle' => 'nullable|string|max:255',
            'variant_data' => 'nullable|array',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'preferred_method' => 'required|string|in:email,sms,both',
        ];
    }

    public function messages(): array
    {
        return [
            'customer_id.required' => 'Customer is required.',
            'customer_id.exists' => 'Selected customer does not exist.',
            'product_type.required' => 'Product type is required.',
            'product_type.in' => 'Product type must be entry, collection, or external.',
            'product_id.required' => 'Product ID is required.',
            'email.required' => 'Email is required.',
            'email.email' => 'Email must be a valid email address.',
            'preferred_method.required' => 'Preferred notification method is required.',
            'preferred_method.in' => 'Preferred method must be email, sms, or both.',
        ];
    }
}
