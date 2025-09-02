<?php

namespace LaravelShopper\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class AssignRoleRequest extends FormRequest
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
            'roles' => 'required|array',
            'roles.*' => 'string|exists:roles,name',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'roles.required' => 'I ruoli sono obbligatori.',
            'roles.array' => 'I ruoli devono essere un array.',
            'roles.*.exists' => 'Uno o più ruoli selezionati non esistono.',
        ];
    }
}
