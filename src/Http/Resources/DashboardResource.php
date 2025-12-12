<?php

declare(strict_types=1);

namespace Cartino\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'period' => $this->resource['period'],
            'sales' => [
                'total_revenue' => round($this->resource['sales']['total_revenue'], 2),
                'total_orders' => $this->resource['sales']['total_orders'],
                'average_order_value' => round($this->resource['sales']['average_order_value'], 2),
                'total_items_sold' => $this->resource['sales']['total_items_sold'],
            ],
            'customers' => $this->resource['customers'],
            'products' => $this->resource['products'],
        ];
    }
}
