<?php

declare(strict_types=1);

namespace Shopper\Workflows\Actions;

use Shopper\Models\Product;

class UpdateProductAction
{
    public function execute(array $data, array $config): void
    {
        $productId = data_get($data, 'product.id') ?? data_get($data, 'id');

        if (! $productId) {
            return;
        }

        $product = Product::find($productId);

        if (! $product) {
            return;
        }

        $product->update($config['updates']);
    }
}
