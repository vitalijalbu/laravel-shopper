<?php

declare(strict_types=1);

namespace Cartino\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductsReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'period' => $this->resource['period'],
            'top_selling' => $this->resource['top_selling']->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'units_sold' => $product->units_sold,
                    'revenue' => round($product->revenue, 2),
                    'current_stock' => $product->stock_quantity,
                ];
            }),
            'top_revenue' => $this->resource['top_revenue']->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'units_sold' => $product->units_sold,
                    'revenue' => round($product->revenue, 2),
                    'average_price' => $product->units_sold > 0 ? round($product->revenue / $product->units_sold, 2) : 0,
                ];
            }),
        ];
    }
}
