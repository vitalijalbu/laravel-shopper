<?php

namespace LaravelShopper\GraphQL\Resolvers;

use LaravelShopper\Models\Product;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class ProductResolver
{
    public function create($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): Product
    {
        $input = $args['input'] ?? $args;
        
        // Extract category IDs if provided
        $categoryIds = $input['categoryIds'] ?? [];
        unset($input['categoryIds']);
        
        // Create the product
        $product = Product::create($input);
        
        // Attach categories if provided
        if (!empty($categoryIds)) {
            $product->categories()->attach($categoryIds);
        }
        
        return $product->fresh(['brand', 'categories', 'media']);
    }

    public function update($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): Product
    {
        $product = Product::findOrFail($args['id']);
        $input = $args['input'] ?? [];
        
        // Extract category IDs if provided
        $categoryIds = $input['categoryIds'] ?? null;
        unset($input['categoryIds']);
        
        // Update the product
        $product->update($input);
        
        // Sync categories if provided
        if ($categoryIds !== null) {
            $product->categories()->sync($categoryIds);
        }
        
        return $product->fresh(['brand', 'categories', 'media']);
    }

    public function delete($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): bool
    {
        $product = Product::findOrFail($args['id']);
        
        // Detach relationships
        $product->categories()->detach();
        
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
