<?php

namespace Shopper\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreBrandRequest extends FormRequest
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
            'name' => 'required|string|max:255|unique:brands,name',
            'slug' => 'nullable|string|max:255|unique:brands,slug',
            'description' => 'nullable|string',
            'logo' => 'nullable|string',
            'website' => 'nullable|url',
            'status' => 'string|in:active,inactive',
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
            'name.required' => 'Il nome del brand è obbligatorio.',
            'name.unique' => 'Questo nome del brand è già in uso.',
            'name.max' => 'Il nome del brand non può superare i 255 caratteri.',
            'website.url' => 'Il sito web deve essere un URL valido.',
            'seo_title.max' => 'Il titolo SEO non può superare i 255 caratteri.',
            'seo_description.max' => 'La descrizione SEO non può superare i 500 caratteri.',
        ];
    }
}
