<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShopifyStyleProductSeeder extends Seeder
{
    public function run()
    {
        // 1. Prodotto semplice senza varianti (es. libro digitale)
        $simpleProduct = DB::table('products')->insertGetId([
            'title' => 'E-book: Laravel Guide',
            'slug' => 'ebook-laravel-guide',
            'handle' => 'ebook-laravel-guide',
            'description' => 'Complete guide to Laravel development',
            'body_html' => '<p>Complete guide to Laravel development with examples</p>',
            'product_type' => 'digital',
            'status' => 'active',
            'published_at' => now(),
            'published_scope' => 'web',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Default variant per prodotto semplice
        $simpleVariant = DB::table('product_variants')->insertGetId([
            'product_id' => $simpleProduct,
            'title' => 'Default Title',
            'sku' => 'EBOOK-LARAVEL-001',
            'price' => 29.99,
            'cost' => 5.00,
            'inventory_quantity' => 999999, // Digitale = illimitato
            'track_quantity' => false,
            'inventory_management' => 'not_managed',
            'inventory_policy' => 'continue',
            'requires_shipping' => false,
            'taxable' => true,
            'position' => 1,
            'status' => 'active',
            'available' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Aggiorna il prodotto con la default variant
        DB::table('products')->where('id', $simpleProduct)->update([
            'default_variant_id' => $simpleVariant,
            'variants_count' => 1,
            'price_min' => 29.99,
            'price_max' => 29.99,
        ]);

        // 2. Prodotto con varianti (es. T-shirt)
        $variantProduct = DB::table('products')->insertGetId([
            'title' => 'Cotton T-Shirt',
            'slug' => 'cotton-t-shirt',
            'handle' => 'cotton-t-shirt',
            'description' => 'Comfortable cotton t-shirt available in multiple colors and sizes',
            'body_html' => '<p>High quality cotton t-shirt, perfect for everyday wear.</p>',
            'product_type' => 'physical',
            'options' => json_encode([
                ['name' => 'Color', 'values' => ['Red', 'Blue', 'Black']],
                ['name' => 'Size', 'values' => ['Small', 'Medium', 'Large']],
            ]),
            'tags' => json_encode(['clothing', 'cotton', 'casual']),
            'status' => 'active',
            'published_at' => now(),
            'published_scope' => 'web',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Varianti per T-shirt (Color x Size = 9 varianti)
        $colors = ['Red', 'Blue', 'Black'];
        $sizes = ['Small', 'Medium', 'Large'];
        $position = 1;
        $prices = [];
        $defaultVariantId = null;

        foreach ($colors as $color) {
            foreach ($sizes as $size) {
                $price = match ($size) {
                    'Small' => 19.99,
                    'Medium' => 21.99,
                    'Large' => 23.99
                };
                $prices[] = $price;

                $variantId = DB::table('product_variants')->insertGetId([
                    'product_id' => $variantProduct,
                    'title' => "{$color} / {$size}",
                    'sku' => 'TSHIRT-'.strtoupper($color).'-'.strtoupper($size),
                    'option1' => $color,
                    'option2' => $size,
                    'price' => $price,
                    'cost' => 8.50,
                    'inventory_quantity' => rand(10, 50),
                    'track_quantity' => true,
                    'inventory_management' => 'shopify',
                    'inventory_policy' => 'deny',
                    'weight' => 0.15,
                    'weight_unit' => 'kg',
                    'dimensions' => json_encode([
                        'length' => 20,
                        'width' => 15,
                        'height' => 2,
                    ]),
                    'requires_shipping' => true,
                    'taxable' => true,
                    'position' => $position,
                    'status' => 'active',
                    'available' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Prima variante = default variant
                if ($position === 1) {
                    $defaultVariantId = $variantId;
                }

                $position++;
            }
        }

        // Aggiorna il prodotto con varianti
        DB::table('products')->where('id', $variantProduct)->update([
            'default_variant_id' => $defaultVariantId,
            'variants_count' => count($colors) * count($sizes),
            'price_min' => min($prices),
            'price_max' => max($prices),
        ]);

        // 3. Prodotto con una sola variante ma con gestione stock
        $singleVariantProduct = DB::table('products')->insertGetId([
            'title' => 'Wireless Headphones',
            'slug' => 'wireless-headphones',
            'handle' => 'wireless-headphones',
            'description' => 'High-quality wireless headphones with noise cancellation',
            'body_html' => '<p>Premium wireless headphones with advanced noise cancellation technology.</p>',
            'product_type' => 'physical',
            'tags' => json_encode(['electronics', 'audio', 'wireless']),
            'status' => 'active',
            'published_at' => now(),
            'published_scope' => 'web',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $headphonesVariant = DB::table('product_variants')->insertGetId([
            'product_id' => $singleVariantProduct,
            'title' => 'Default Title',
            'sku' => 'HEADPHONES-WH-001',
            'price' => 149.99,
            'compare_at_price' => 199.99, // Prezzo scontato
            'cost' => 75.00,
            'inventory_quantity' => 25,
            'track_quantity' => true,
            'inventory_management' => 'shopify',
            'inventory_policy' => 'deny',
            'weight' => 0.35,
            'weight_unit' => 'kg',
            'dimensions' => json_encode([
                'length' => 18,
                'width' => 20,
                'height' => 8,
            ]),
            'requires_shipping' => true,
            'taxable' => true,
            'position' => 1,
            'status' => 'active',
            'available' => true,
            'metafields' => json_encode([
                'warranty' => '2 years',
                'battery_life' => '30 hours',
                'connectivity' => 'Bluetooth 5.0',
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('products')->where('id', $singleVariantProduct)->update([
            'default_variant_id' => $headphonesVariant,
            'variants_count' => 1,
            'price_min' => 149.99,
            'price_max' => 149.99,
        ]);

        echo "✅ Creati 3 prodotti Shopify-style:\n";
        echo "   - E-book (prodotto digitale senza varianti fisiche)\n";
        echo "   - T-shirt (9 varianti: 3 colori x 3 taglie)\n";
        echo "   - Cuffie (singola variante con gestione stock)\n";
    }
}
