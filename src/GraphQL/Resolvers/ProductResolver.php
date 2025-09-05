<?php

names        $input = $args['input'];

        // Create the product
        $product = Product::create($input);

        return $product->fresh(['brand', 'media']);aphQL\Resolvers;

use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Shopper\Models\Product;

class ProductResolver
{
    public function create($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): Product
    {
        $input = $args['input'] ?? $args;

        // Create the product
        $product = Product::create($input);

        return $product->fresh(['brand', 'media']);
    }

    public function update($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): Product
    {
        $product = Product::findOrFail($args['id']);
        $input = $args['input'] ?? [];

        // Update the product
        $product->update($input);

        return $product->fresh(['brand', 'media']);
    }

    public function delete($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): bool
    {
        $product = Product::findOrFail($args['id']);

        // Delete the product
        return $product->delete();
    }

    public function formattedPrice($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): string
    {
        return $rootValue->formatted_price;
    }

    public function formattedComparePrice($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): ?string
    {
        return $rootValue->formatted_compare_price;
    }

    public function inStock($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): bool
    {
        return $rootValue->in_stock;
    }

    public function isOnSale($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): bool
    {
        return $rootValue->is_on_sale;
    }
}
