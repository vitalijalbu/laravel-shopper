<?php

declare(strict_types=1);

namespace Cartino\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreSiteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', \Cartino\Models\Site::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'handle' => ['required', 'string', 'max:255', 'unique:sites,handle'],
            'name' => ['required', 'string', 'max:255'],
            'locale' => ['required', 'string', 'max:10'],
            'is_default' => ['nullable', 'boolean'],
            'status' => ['nullable', 'in:active,inactive'],
        ];
    }
}
