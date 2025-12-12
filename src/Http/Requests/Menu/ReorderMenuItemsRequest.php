<?php

namespace Cartino\Http\Requests\Menu;

use Illuminate\Foundation\Http\FormRequest;

class ReorderMenuItemsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items' => 'required|array',
            'items.*.id' => 'required|integer|exists:menu_items,id',
            'items.*.children' => 'nullable|array',
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'Items array is required.',
            'items.*.id.required' => 'Each item must have an id.',
            'items.*.id.exists' => 'One or more menu items do not exist.',
        ];
    }
}
