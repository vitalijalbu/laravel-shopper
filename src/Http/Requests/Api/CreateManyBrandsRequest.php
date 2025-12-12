<?php

declare(strict_types=1);

namespace Cartino\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class CreateManyBrandsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'brands' => 'required|array|min:1|max:100',
            'brands.*.name' => 'required|string|max:255',
            'brands.*.slug' => 'required|string|max:255|unique:brands,slug',
            'brands.*.status' => 'sometimes|in:active,inactive',
            'brands.*.description' => 'nullable|string|max:1000',
            'brands.*.website' => 'nullable|url|max:255',
            'brands.*.email' => 'nullable|email|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'brands.required' => 'È necessario fornire almeno un brand',
            'brands.max' => 'Non puoi creare più di 100 brand alla volta',
            'brands.*.name.required' => 'Il nome è obbligatorio per ogni brand',
            'brands.*.slug.unique' => 'Lo slug deve essere unico per ogni brand',
            'brands.*.status.in' => 'Lo status deve essere active o inactive',
        ];
    }

    public function getBrandsData(): array
    {
        return $this->validated()['brands'];
    }
}
