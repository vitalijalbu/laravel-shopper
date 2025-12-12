<?php

declare(strict_types=1);

namespace Cartino\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class DestroyManyBrandsRequest extends FormRequest
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
            'force' => 'sometimes|boolean', // Force delete anche se ci sono dipendenze
        ];
    }

    public function messages(): array
    {
        return [
            'ids.required' => 'È necessario fornire almeno un ID',
            'ids.max' => 'Non puoi eliminare più di 100 brand alla volta',
            'ids.*.exists' => 'Uno o più brand non esistono',
        ];
    }

    public function getIds(): array
    {
        return $this->validated()['ids'];
    }

    public function isForceDelete(): bool
    {
        return $this->validated()['force'] ?? false;
    }
}
