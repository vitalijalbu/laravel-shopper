<?php

namespace Shopper\Http\Requests\Menu;

use Illuminate\Foundation\Http\FormRequest;

class StoreMenuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'handle' => 'nullable|string|max:255|unique:menus,handle',
            'description' => 'nullable|string',
            'settings' => 'nullable|array',
            'is_active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => __('shopper::validation.menu.title.required'),
            'title.string' => __('shopper::validation.menu.title.string'),
            'title.max' => __('shopper::validation.menu.title.max'),
            'handle.string' => __('shopper::validation.menu.handle.string'),
            'handle.max' => __('shopper::validation.menu.handle.max'),
            'handle.unique' => __('shopper::validation.menu.handle.unique'),
            'description.string' => __('shopper::validation.menu.description.string'),
            'settings.array' => __('shopper::validation.menu.settings.array'),
            'is_active.boolean' => __('shopper::validation.menu.is_active.boolean'),
        ];
    }
}
