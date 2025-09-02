<?php

namespace Shopper\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBrandRequest extends FormRequest
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
        $brandId = $this->route('brand');
        
        return [
            'name' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('brands', 'name')->ignore($brandId)
            ],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('brands', 'slug')->ignore($brandId)
            ],
            'description' => 'nullable|string',
            'logo' => 'nullable|string',
            'website' => 'nullable|url',
            'is_enabled' => 'boolean',
            'is_featured' => 'boolean',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.unique' => 'Questo nome del brand è già in uso.',
            'name.max' => 'Il nome del brand non può superare i 255 caratteri.',
            'website.url' => 'Il sito web deve essere un URL valido.',
            'seo_title.max' => 'Il titolo SEO non può superare i 255 caratteri.',
            'seo_description.max' => 'La descrizione SEO non può superare i 500 caratteri.',
        ];
    }
}
