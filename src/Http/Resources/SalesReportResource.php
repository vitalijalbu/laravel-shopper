<?php

declare(strict_types=1);

namespace Cartino\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalesReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'period' => $this->resource['period'],
            'summary' => [
                'total_revenue' => round($this->resource['summary']['total_revenue'], 2),
                'total_orders' => $this->resource['summary']['total_orders'],
                'average_order_value' => round($this->resource['summary']['average_order_value'], 2),
                'total_items_sold' => $this->resource['summary']['total_items_sold'],
            ],
            'data' => $this->resource['data']->map(function ($item) {
                return [
                    'period' => $item->period,
                    'orders_count' => $item->orders_count,
                    'revenue' => round($item->revenue, 2),
                    'average_order_value' => round($item->average_order_value, 2),
                    'items_sold' => $item->items_sold,
                ];
            }),
        ];
    }
}
