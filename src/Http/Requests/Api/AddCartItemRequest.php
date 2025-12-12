<?php

declare(strict_types=1);

namespace Cartino\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class AddCartItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'product_variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'options' => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required' => 'Il prodotto è obbligatorio',
            'product_id.exists' => 'Il prodotto selezionato non esiste',
            'product_variant_id.exists' => 'La variante selezionata non esiste',
            'quantity.required' => 'La quantità è obbligatoria',
            'quantity.min' => 'La quantità minima è 1',
        ];
    }
}
