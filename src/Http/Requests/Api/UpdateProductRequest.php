<?php

declare(strict_types=1);

namespace Cartino\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $productId = $this->route('product');

        return [
            'site_id' => ['sometimes', 'integer', 'exists:sites,id'],
            'brand_id' => ['nullable', 'integer', 'exists:brands,id'],
            'product_type_id' => ['nullable', 'integer', 'exists:product_types,id'],
            'name' => ['sometimes', 'string', 'max:255'],
            'slug' => ['sometimes', 'string', 'max:255', "unique:products,slug,{$productId}"],
            'sku' => ['sometimes', 'string', 'max:100', "unique:products,sku,{$productId}"],
            'description' => ['nullable', 'string'],
            'short_description' => ['nullable', 'string'],
            'price_amount' => ['sometimes', 'numeric', 'min:0'],
            'sale_price_amount' => ['nullable', 'numeric', 'min:0'],
            'cost_amount' => ['nullable', 'numeric', 'min:0'],
            'currency_code' => ['sometimes', 'string', 'size:3'],
            'status' => ['sometimes', 'in:draft,published,archived'],
            'is_visible' => ['boolean'],
            'is_featured' => ['boolean'],
            'stock_quantity' => ['nullable', 'integer', 'min:0'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'length' => ['nullable', 'numeric', 'min:0'],
            'width' => ['nullable', 'numeric', 'min:0'],
            'height' => ['nullable', 'numeric', 'min:0'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'published_at' => ['nullable', 'date'],
        ];
    }
}
