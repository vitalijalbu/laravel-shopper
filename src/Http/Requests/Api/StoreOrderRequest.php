<?php

namespace Shopper\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
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
            'customer_id' => 'nullable|integer|exists:customers,id',
            'currency_id' => 'required|integer|exists:currencies,id',
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
            'payment_status' => 'required|in:pending,paid,partially_paid,refunded,cancelled',
            'fulfillment_status' => 'required|in:pending,processing,shipped,delivered,cancelled',
            'notes' => 'nullable|string',
            'shipping_address' => 'required|array',
            'billing_address' => 'required|array',
            'lines' => 'required|array|min:1',
            'lines.*.product_id' => 'required|integer|exists:products,id',
            'lines.*.product_variant_id' => 'nullable|integer|exists:product_variants,id',
            'lines.*.quantity' => 'required|integer|min:1',
            'lines.*.unit_price' => 'required|numeric|min:0',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'customer_id.exists' => 'Il cliente selezionato non esiste.',
            'currency_id.required' => 'La valuta è obbligatoria.',
            'currency_id.exists' => 'La valuta selezionata non esiste.',
            'status.required' => 'Lo stato dell\'ordine è obbligatorio.',
            'status.in' => 'Lo stato dell\'ordine deve essere uno tra: pending, processing, shipped, delivered, cancelled.',
            'payment_status.required' => 'Lo stato del pagamento è obbligatorio.',
            'payment_status.in' => 'Lo stato del pagamento deve essere uno tra: pending, paid, partially_paid, refunded, cancelled.',
            'fulfillment_status.required' => 'Lo stato di evasione è obbligatorio.',
            'fulfillment_status.in' => 'Lo stato di evasione deve essere uno tra: pending, processing, shipped, delivered, cancelled.',
            'shipping_address.required' => 'L\'indirizzo di spedizione è obbligatorio.',
            'billing_address.required' => 'L\'indirizzo di fatturazione è obbligatorio.',
            'lines.required' => 'Le righe dell\'ordine sono obbligatorie.',
            'lines.min' => 'L\'ordine deve contenere almeno una riga.',
            'lines.*.product_id.required' => 'L\'ID del prodotto è obbligatorio per ogni riga.',
            'lines.*.product_id.exists' => 'Il prodotto selezionato non esiste.',
            'lines.*.quantity.required' => 'La quantità è obbligatoria per ogni riga.',
            'lines.*.quantity.min' => 'La quantità deve essere almeno 1.',
            'lines.*.unit_price.required' => 'Il prezzo unitario è obbligatorio per ogni riga.',
            'lines.*.unit_price.min' => 'Il prezzo unitario deve essere maggiore o uguale a 0.',
        ];
    }
}
