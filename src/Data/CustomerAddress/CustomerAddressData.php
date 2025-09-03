<?php

namespace Shopper\Data\CustomerAddress;

use Spatie\LaravelData\Data;

class CustomerAddressData extends Data
{
    public function __construct(
        public ?int $id,
        public int $customer_id,
        public string $type,
        public string $first_name,
        public string $last_name,
        public ?string $company,
        public string $address_line_1,
        public ?string $address_line_2,
        public string $city,
        public ?string $state,
        public string $postal_code,
        public string $country_code,
        public ?string $phone,
        public bool $is_default,
        public ?string $created_at,
        public ?string $updated_at,
    ) {}

    public static function fromModel(\Shopper\Models\CustomerAddress $address): self
    {
        return new self(
            id: $address->id,
            customer_id: $address->customer_id,
            type: $address->type,
            first_name: $address->first_name,
            last_name: $address->last_name,
            company: $address->company,
            address_line_1: $address->address_line_1,
            address_line_2: $address->address_line_2,
            city: $address->city,
            state: $address->state,
            postal_code: $address->postal_code,
            country_code: $address->country_code,
            phone: $address->phone,
            is_default: $address->is_default,
            created_at: $address->created_at?->toISOString(),
            updated_at: $address->updated_at?->toISOString(),
        );
    }
}
