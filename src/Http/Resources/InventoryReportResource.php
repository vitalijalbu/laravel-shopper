<?php

declare(strict_types=1);

namespace Cartino\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'summary' => [
                'total_products' => $this->resource['summary']['total_products'],
                'in_stock' => $this->resource['summary']['in_stock'],
                'low_stock' => $this->resource['summary']['low_stock'],
                'out_of_stock' => $this->resource['summary']['out_of_stock'],
                'total_inventory_value' => round($this->resource['summary']['total_inventory_value'], 2),
            ],
            'low_stock_products' => $this->resource['low_stock_products']->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'stock_quantity' => $product->stock_quantity,
                    'price_amount' => round($product->price_amount, 2),
                ];
            }),
            'out_of_stock_products' => $this->resource['out_of_stock_products']->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'price_amount' => round($product->price_amount, 2),
                ];
            }),
        ];
    }
}
