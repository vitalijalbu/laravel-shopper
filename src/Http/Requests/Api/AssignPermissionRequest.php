<?php

namespace Shopper\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class AssignPermissionRequest extends FormRequest
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
            'permissions' => 'required|array',
            'permissions.*' => 'string|exists:permissions,name',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'permissions.required' => 'I permessi sono obbligatori.',
            'permissions.array' => 'I permessi devono essere un array.',
            'permissions.*.exists' => 'Uno o più permessi selezionati non esistono.',
        ];
    }
}
