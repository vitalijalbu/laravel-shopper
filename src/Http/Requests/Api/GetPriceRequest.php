<?php

declare(strict_types=1);

namespace Cartino\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class GetPriceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Variant identification
            'variant_id' => ['sometimes', 'integer', 'exists:product_variants,id'],
            'sku' => ['sometimes', 'string', 'exists:product_variants,sku'],

            // Context (optional, will use session if not provided)
            'market_id' => ['sometimes', 'integer', 'exists:markets,id'],
            'market_code' => ['sometimes', 'string', 'exists:markets,code'],
            'site_id' => ['sometimes', 'integer', 'exists:sites,id'],
            'channel_id' => ['sometimes', 'integer', 'exists:channels,id'],
            'catalog_id' => ['sometimes', 'integer', 'exists:catalogs,id'],
            'currency' => ['sometimes', 'string', 'size:3'],
            'locale' => ['sometimes', 'string', 'max:10'],
            'quantity' => ['sometimes', 'integer', 'min:1', 'max:10000'],

            // Options
            'include_tiers' => ['sometimes', 'boolean'],
            'include_context' => ['sometimes', 'boolean'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $validated = parent::validated($key, $default);

        // Default quantity to 1
        if (! isset($validated['quantity'])) {
            $validated['quantity'] = 1;
        }

        return $key ? ($validated[$key] ?? $default) : $validated;
    }
}
