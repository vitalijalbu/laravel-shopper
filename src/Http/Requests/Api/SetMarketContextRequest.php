<?php

declare(strict_types=1);

namespace Cartino\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class SetMarketContextRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'market_id' => ['sometimes', 'integer', 'exists:markets,id'],
            'market_code' => ['sometimes', 'string', 'exists:markets,code'],
            'site_id' => ['sometimes', 'integer', 'exists:sites,id'],
            'channel_id' => ['sometimes', 'integer', 'exists:channels,id'],
            'catalog_id' => ['sometimes', 'integer', 'exists:catalogs,id'],
            'currency' => ['sometimes', 'string', 'size:3'],
            'locale' => ['sometimes', 'string', 'max:10'],
            'country_code' => ['sometimes', 'string', 'size:2'],
            'quantity' => ['sometimes', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'market_id.exists' => 'The selected market does not exist.',
            'market_code.exists' => 'The selected market code does not exist.',
            'site_id.exists' => 'The selected site does not exist.',
            'channel_id.exists' => 'The selected channel does not exist.',
            'catalog_id.exists' => 'The selected catalog does not exist.',
            'currency.size' => 'Currency must be a 3-letter ISO code.',
            'country_code.size' => 'Country code must be a 2-letter ISO code.',
            'quantity.min' => 'Quantity must be at least 1.',
        ];
    }
}
