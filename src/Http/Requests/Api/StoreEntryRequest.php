<?php

declare(strict_types=1);

namespace Cartino\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'collection' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9-]+$/',
                Rule::unique('entries')->where(function ($query) {
                    return $query->where('collection', $this->collection)
                        ->where('locale', $this->locale ?? 'it');
                }),
            ],
            'title' => ['required', 'string', 'max:255'],
            'data' => ['nullable', 'array'],
            'status' => ['required', 'in:draft,published,scheduled'],
            'published_at' => ['nullable', 'date'],
            'author_id' => ['nullable', 'integer', 'exists:users,id'],
            'locale' => ['nullable', 'string', 'size:2'],
            'parent_id' => ['nullable', 'integer', 'exists:entries,id'],
            'order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
