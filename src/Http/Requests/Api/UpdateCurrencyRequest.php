<?php

declare(strict_types=1);

namespace Cartino\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCurrencyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $currencyId = $this->route('currency');

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'code' => ['sometimes', 'string', 'size:3', "unique:currencies,code,{$currencyId}"],
            'symbol' => ['sometimes', 'string', 'max:10'],
            'decimal_places' => ['sometimes', 'integer', 'min:0', 'max:4'],
            'exchange_rate' => ['sometimes', 'numeric', 'min:0'],
            'is_enabled' => ['boolean'],
            'is_default' => ['boolean'],
        ];
    }
}
