<?php

declare(strict_types=1);

namespace Cartino\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $entryId = $this->route('entry');

        return [
            'collection' => ['sometimes', 'string', 'max:255'],
            'slug' => [
                'sometimes',
                'string',
                'max:255',
                'regex:/^[a-z0-9-]+$/',
                Rule::unique('entries')->where(function ($query) use ($entryId) {
                    return $query->where('collection', $this->collection ?? $this->route('entry')->collection)
                        ->where('locale', $this->locale ?? $this->route('entry')->locale)
                        ->where('id', '!=', $entryId);
                }),
            ],
            'title' => ['sometimes', 'string', 'max:255'],
            'data' => ['nullable', 'array'],
            'status' => ['sometimes', 'in:draft,published,scheduled'],
            'published_at' => ['nullable', 'date'],
            'author_id' => ['nullable', 'integer', 'exists:users,id'],
            'locale' => ['nullable', 'string', 'size:2'],
            'parent_id' => ['nullable', 'integer', 'exists:entries,id'],
            'order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
