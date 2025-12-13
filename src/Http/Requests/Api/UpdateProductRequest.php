<?php

declare(strict_types=1);

namespace Cartino\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        $product = $this->route('product');

        return $this->user()?->can('update', $product) ?? false;
    }

    public function rules(): array
    {
        $productId = $this->route('product')->id ?? $this->route('product');

        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'slug' => ['sometimes', 'string', 'max:255', "unique:products,slug,{$productId}"],
            'handle' => ['nullable', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'product_type' => ['sometimes', 'in:physical,digital,service'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'product_type_id' => ['nullable', 'exists:product_types,id'],
            'status' => ['sometimes', 'in:draft,published,archived'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'options' => ['nullable', 'array'],
            'tags' => ['nullable', 'array'],
            'requires_selling_plan' => ['nullable', 'boolean'],
            'published_at' => ['nullable', 'date'],
        ];
    }
}
