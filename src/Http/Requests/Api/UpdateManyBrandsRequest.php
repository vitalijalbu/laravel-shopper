<?php

declare(strict_types=1);

namespace Cartino\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateManyBrandsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ids' => 'required|array|min:1|max:100',
            'ids.*' => 'integer|exists:brands,id',
            'data' => 'required|array',
            'data.name' => 'sometimes|string|max:255',
            'data.status' => 'sometimes|in:active,inactive',
            'data.description' => 'nullable|string|max:1000',
            'data.website' => 'nullable|url|max:255',
            'data.email' => 'nullable|email|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'ids.required' => 'È necessario fornire almeno un ID',
            'ids.max' => 'Non puoi aggiornare più di 100 brand alla volta',
            'ids.*.exists' => 'Uno o più brand non esistono',
            'data.required' => 'È necessario fornire i dati da aggiornare',
        ];
    }

    public function getIds(): array
    {
        return $this->validated()['ids'];
    }

    public function getUpdateData(): array
    {
        return $this->validated()['data'];
    }
}
