<?php

namespace LaravelShopper\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class BulkActionRequest extends FormRequest
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
            'action' => 'required|string',
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer',
            'metadata' => 'nullable|array',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'action.required' => 'L\'azione è obbligatoria.',
            'ids.required' => 'Gli ID sono obbligatori.',
            'ids.array' => 'Gli ID devono essere un array.',
            'ids.min' => 'Devi selezionare almeno un elemento.',
            'ids.*.integer' => 'Ogni ID deve essere un numero intero.',
            'metadata.array' => 'I metadati devono essere un array.',
        ];
    }
}
