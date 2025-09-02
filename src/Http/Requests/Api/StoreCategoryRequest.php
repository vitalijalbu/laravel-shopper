<?php

namespace Shopper\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|integer|exists:categories,id',
            'description' => 'nullable|string',
            'is_enabled' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Il nome della categoria è obbligatorio.',
            'name.max' => 'Il nome della categoria non può superare i 255 caratteri.',
            'parent_id.exists' => 'La categoria padre selezionata non esiste.',
            'sort_order.min' => 'L\'ordine di visualizzazione deve essere maggiore o uguale a 0.',
            'seo_title.max' => 'Il titolo SEO non può superare i 255 caratteri.',
            'seo_description.max' => 'La descrizione SEO non può superare i 500 caratteri.',
        ];
    }
}
