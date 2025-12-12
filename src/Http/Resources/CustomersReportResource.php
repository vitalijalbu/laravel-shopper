<?php

declare(strict_types=1);

namespace Cartino\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomersReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'period' => $this->resource['period'],
            'summary' => [
                'new_customers' => $this->resource['summary']['new_customers'],
                'total_customers' => $this->resource['summary']['total_customers'],
                'average_ltv' => round($this->resource['summary']['average_ltv'], 2),
            ],
            'new_customers_timeline' => $this->resource['new_customers_timeline'],
            'top_customers' => $this->resource['top_customers']->map(function ($customer) {
                return [
                    'id' => $customer['id'],
                    'name' => $customer['name'],
                    'email' => $customer['email'],
                    'orders_count' => $customer['orders_count'],
                    'total_spent' => round($customer['total_spent'], 2),
                ];
            }),
        ];
    }
}
