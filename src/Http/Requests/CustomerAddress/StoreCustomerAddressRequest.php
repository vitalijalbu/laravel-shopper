<?php

namespace Shopper\Http\Requests\CustomerAddress;

use Illuminate\Foundation\Http\FormRequest;
use Shopper\Enums\AddressType;
use Illuminate\Validation\Rule;

class StoreCustomerAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(AddressType::class)],
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'nullable|string|max:255',
            'postal_code' => 'required|string|max:20',
            'country_code' => 'required|string|size:2',
            'phone' => 'nullable|string|max:20',
            'is_default' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => __('shopper::validation.address.type.required'),
            'type.enum' => __('shopper::validation.address.type.enum'),
            'first_name.required' => __('shopper::validation.address.first_name.required'),
            'first_name.string' => __('shopper::validation.address.first_name.string'),
            'first_name.max' => __('shopper::validation.address.first_name.max'),
            'last_name.required' => __('shopper::validation.address.last_name.required'),
            'last_name.string' => __('shopper::validation.address.last_name.string'),
            'last_name.max' => __('shopper::validation.address.last_name.max'),
            'company.string' => __('shopper::validation.address.company.string'),
            'company.max' => __('shopper::validation.address.company.max'),
            'address_line_1.required' => __('shopper::validation.address.address_line_1.required'),
            'address_line_1.string' => __('shopper::validation.address.address_line_1.string'),
            'address_line_1.max' => __('shopper::validation.address.address_line_1.max'),
            'address_line_2.string' => __('shopper::validation.address.address_line_2.string'),
            'address_line_2.max' => __('shopper::validation.address.address_line_2.max'),
            'city.required' => __('shopper::validation.address.city.required'),
            'city.string' => __('shopper::validation.address.city.string'),
            'city.max' => __('shopper::validation.address.city.max'),
            'state.string' => __('shopper::validation.address.state.string'),
            'state.max' => __('shopper::validation.address.state.max'),
            'postal_code.required' => __('shopper::validation.address.postal_code.required'),
            'postal_code.string' => __('shopper::validation.address.postal_code.string'),
            'postal_code.max' => __('shopper::validation.address.postal_code.max'),
            'country_code.required' => __('shopper::validation.address.country_code.required'),
            'country_code.string' => __('shopper::validation.address.country_code.string'),
            'country_code.size' => __('shopper::validation.address.country_code.size'),
            'phone.string' => __('shopper::validation.address.phone.string'),
            'phone.max' => __('shopper::validation.address.phone.max'),
            'is_default.boolean' => __('shopper::validation.address.is_default.boolean'),
        ];
    }
}
