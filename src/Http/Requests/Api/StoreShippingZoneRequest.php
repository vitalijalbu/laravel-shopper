<?php

declare(strict_types=1);

namespace Cartino\Http\Requests\Api;

use Cartino\Models\Site;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class StoreShippingZoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'site_id' => ['required', 'integer', Rule::exists(Site::class, 'id')],
            'name' => ['required', 'string', 'max:255'],
            'countries' => ['required', 'array', 'min:1'],
            'countries.*' => ['string', 'size:2'],
        ];
    }
}
