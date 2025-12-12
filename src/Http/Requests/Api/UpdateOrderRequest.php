<?php

namespace Cartino\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Gestito da middleware di autenticazione
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'status' => 'sometimes|in:pending,processing,shipped,delivered,cancelled',
            'payment_status' => 'sometimes|in:pending,paid,partially_paid,refunded,cancelled',
            'fulfillment_status' => 'sometimes|in:pending,processing,shipped,delivered,cancelled',
            'notes' => 'nullable|string',
            'shipping_address' => 'sometimes|array',
            'billing_address' => 'sometimes|array',
            'tracking_number' => 'nullable|string',
            'shipped_at' => 'nullable|date',
            'delivered_at' => 'nullable|date',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'status.in' => 'Lo stato dell\'ordine deve essere uno tra: pending, processing, shipped, delivered, cancelled.',
            'payment_status.in' => 'Lo stato del pagamento deve essere uno tra: pending, paid, partially_paid, refunded, cancelled.',
            'fulfillment_status.in' => 'Lo stato di evasione deve essere uno tra: pending, processing, shipped, delivered, cancelled.',
            'shipping_address.array' => 'L\'indirizzo di spedizione deve essere un array.',
            'billing_address.array' => 'L\'indirizzo di fatturazione deve essere un array.',
            'shipped_at.date' => 'La data di spedizione deve essere una data valida.',
            'delivered_at.date' => 'La data di consegna deve essere una data valida.',
        ];
    }
}
