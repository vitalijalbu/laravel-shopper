<?php

declare(strict_types=1);

namespace Cartino\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RevenueReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'period' => $this->resource['period'],
            'summary' => [
                'gross_sales' => round($this->resource['summary']['gross_sales'], 2),
                'discounts' => round($this->resource['summary']['discounts'], 2),
                'taxes' => round($this->resource['summary']['taxes'], 2),
                'shipping' => round($this->resource['summary']['shipping'], 2),
                'net_sales' => round($this->resource['summary']['net_sales'], 2),
                'orders_count' => $this->resource['summary']['orders_count'],
            ],
            'timeline' => $this->resource['timeline']->map(function ($item) {
                return [
                    'period' => $item->period,
                    'gross_sales' => round($item->gross_sales, 2),
                    'discounts' => round($item->discounts, 2),
                    'taxes' => round($item->taxes, 2),
                    'shipping' => round($item->shipping, 2),
                    'net_sales' => round($item->net_sales, 2),
                    'orders_count' => $item->orders_count,
                ];
            }),
        ];
    }
}
