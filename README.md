# Laravel Shopper

# Laravel Shopper

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![Laravel Version](https://img.shields.io/badge/Laravel-11.0-red.svg)](https://laravel.com)
[![PHP Version](https://img.shields.io/badge/PHP-8.3+-blue.svg)](https://php.net)

A complete e-commerce platform for Laravel, inspired by **Shopify** and **Statamic CMS**, combining the best of both worlds with modern Laravel architecture.

## 🚀 Features

### 🏪 E-commerce Core

- **Multi-site Architecture**: Manage multiple stores from one installation
- **Products & Variants**: Full product catalog with variants, inventory tracking
- **Orders & Fulfillment**: Complete order management with Shopify-style workflow
- **Customer Management**: Customer accounts, segments, and analytics
- **Inventory Tracking**: Real-time inventory with adjustments and transfers
- **Categories & Collections**: Flexible product organization

### 🎨 Storefront Builder

- **Template Engine**: Database-driven templates with Blade + Vue.js components
- **Visual Page Builder**: Drag-and-drop interface like Shopify's Online Store 2.0
- **Sections & Blocks**: Reusable UI components with JSON schema configuration
- **Custom Templates**: Assign different templates to products, collections, pages
- **Theme System**: Multi-theme support with customizable settings

### 🎛️ Control Panel (CP)

- **Shopify-inspired Dashboard**: Clean, intuitive interface identical to Shopify
- **Extensible Pages**: Custom actions, UI blocks, and component system
- **Advanced Navigation**: Collapsible sections, badges, permissions like Statamic
- **Content Management**: Pages, blog posts, navigation menus
- **Analytics**: Sales reports, customer insights, performance metrics

### 🔧 Developer Experience

- **API-First**: RESTful APIs for headless commerce
- **Multi-tenancy**: Site isolation with performance optimization
- **Handle-based Routing**: SEO-friendly URLs with ID fallback
- **Modern Stack**: Laravel 11, Inertia.js, Vue 3, Tailwind CSS
- **Database Performance**: PostgreSQL with JSONB, GIN indexes

## 📦 Installation

```bash
composer require vitalijalbu/laravel-shopper
```

### Publish and Run Migrations

```bash
php artisan vendor:publish --provider="LaravelShopper\ShopperServiceProvider" --tag="migrations"
php artisan migrate
```

### Publish Assets

```bash
php artisan vendor:publish --provider="LaravelShopper\ShopperServiceProvider" --tag="assets"
npm install && npm run build
```

### Create Admin User

```bash
php artisan shopper:install
```

## 🎯 Quick Start

### 1. Access Control Panel

Visit `/cp` to access the Shopify-style control panel.

### 2. Create Your First Product

```php
use LaravelShopper\Models\Product;

$product = Product::create([
    'site_id' => 1,
    'name' => 'Awesome T-Shirt',
    'handle' => 'awesome-t-shirt',
    'description' => 'The most comfortable t-shirt ever made.',
    'price' => 2999, // in cents
    'status' => 'active',
]);
```

### 3. Create a Custom Template

```php
use LaravelShopper\Models\StorefrontTemplate;

$template = StorefrontTemplate::create([
    'site_id' => 1,
    'handle' => 'product-premium',
    'name' => 'Premium Product Template',
    'type' => 'product',
    'sections' => [
        [
            'type' => 'hero',
            'settings' => ['layout' => 'full-width'],
            'blocks' => [
                ['type' => 'image', 'settings' => ['src' => '{{product.featured_image}}']]
            ]
        ]
    ]
]);
```

### 4. Build Custom CP Pages

```php
use LaravelShopper\CP\Page;

$page = Page::make('My Custom Page')
    ->primaryAction('Save', '/save-url')
    ->card('Statistics')
    ->content('StatsChart', ['data' => $chartData]);

return Inertia::render('CP/CustomPage', [
    'page' => $page->compile()
]);
```

## 🎨 Storefront Templates

### Template Types

| Template Type | Purpose | Example URL |
|---------------|---------|-------------|
| `index` | Homepage | `/` |
| `product` | Product pages | `/products/awesome-shirt` |
| `collection` | Category pages | `/collections/t-shirts` |
| `page` | Static pages | `/pages/about-us` |
| `blog` | Blog listing | `/blog` |
| `article` | Blog posts | `/blog/our-story` |

### Creating Sections

```php
use LaravelShopper\Models\StorefrontSection;

$section = StorefrontSection::create([
    'site_id' => 1,
    'handle' => 'hero',
    'name' => 'Hero Section',
    'component_path' => 'sections/Hero.vue',
    'schema' => [
        'settings' => [
            [
                'type' => 'text',
                'id' => 'heading',
                'label' => 'Heading',
                'default' => 'Welcome to our store'
            ],
            [
                'type' => 'select',
                'id' => 'layout',
                'label' => 'Layout',
                'options' => [
                    ['value' => 'centered', 'label' => 'Centered'],
                    ['value' => 'full-width', 'label' => 'Full Width']
                ]
            ]
        ]
    ],
    'blocks' => [
        'image' => ['name' => 'Image', 'limit' => 1],
        'text' => ['name' => 'Text', 'limit' => 3]
    ]
]);
```

## 🎛️ Control Panel Extensions

### Custom Dashboard Cards

```php
use LaravelShopper\CP\Dashboard;

Dashboard::card('SalesChart', [
    'title' => 'Sales Overview',
    'period' => '30d'
], 10);

Dashboard::metric('Total Sales', function () {
    return Order::sum('total');
}, 'dollar-sign', 'green');
```

### Navigation Items

```php
use LaravelShopper\CP\Navigation;

Navigation::section('custom', 'My Section')
    ->order(50);

Navigation::item('custom.reports')
    ->label('Custom Reports')
    ->icon('bar-chart')
    ->url('/cp/custom/reports')
    ->section('custom')
    ->permissions(['view-reports']);
```

### Extensible Pages

```php
use LaravelShopper\CP\Page;

$page = Page::make('Advanced Product')
    ->primaryAction('Save Product', null, ['form' => 'product-form'])
    ->secondaryActions([
        ['label' => 'Duplicate', 'url' => '/duplicate'],
        ['label' => 'Delete', 'destructive' => true]
    ])
    ->tab('details', 'Details', 'ProductDetails')
    ->tab('inventory', 'Inventory', 'ProductInventory')
    ->tab('seo', 'SEO', 'ProductSEO');

// Two-column layout
$layout = $page->layout();
$layout->twoColumns()
    ->primary('ProductForm', ['product' => $product])
    ->secondary('ProductStatus')
    ->secondary('ProductVisibility');
```

## 🛠️ Architecture

### Multi-site Structure

```php
Site::create([
    'name' => 'Main Store',
    'handle' => 'main',
    'domain' => 'shop.example.com',
    'locale' => 'en',
    'currency' => 'USD',
    'is_default' => true
]);
```

### Handle-based Routing

All models support both ID and handle-based routing:

```php
// Both work automatically
Route::get('/products/{handle}', [ProductController::class, 'show']);

// Resolves: /products/123 OR /products/awesome-shirt
```

### Performance Optimizations

- **JSONB Fields**: PostgreSQL-optimized JSON storage
- **GIN Indexes**: Full-text search on JSON fields
- **Composite Indexes**: Multi-column query optimization
- **Site Isolation**: Tenant-scoped queries

## 📊 API Reference

### Products API

```http
GET /api/products
GET /api/products/{handle}
POST /api/products
PUT /api/products/{handle}
DELETE /api/products/{handle}
```

### Storefront API

```http
GET /api/storefront/products
GET /api/storefront/collections
GET /api/storefront/pages
POST /api/storefront/cart/add
```

## 🧪 Testing & Development

```bash
composer test
```

## 🔧 Configuration

Publish the config file:

```bash
php artisan vendor:publish --provider="LaravelShopper\ShopperServiceProvider" --tag="config"
```

### Key Configuration Options

```php
// config/shopper.php
return [
    'multi_site' => true,
    'default_currency' => 'USD',
    'handle_routing' => true,
    'template_engine' => 'hybrid', // hybrid, blade, vue
    'cp_path' => 'cp',
    'api_path' => 'api',
];
```

## 🎨 Frontend Integration

### Vue 3 Components

```vue
<template>
  <section class="hero">
    <h1>{{ settings.heading }}</h1>
    <img :src="block.settings.image" />
  </section>
</template>

<script setup>
defineProps(['settings', 'blocks'])
</script>
```

### Blade Templates

```blade
@extends('layouts.app')

@section('content')
    <div class="product-page">
        <h1>{{ $product->name }}</h1>
        <div class="price">${{ $product->price / 100 }}</div>
    </div>
@endsection
```

## 🤝 Contributing

1. Fork the project
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- **Shopify**: Inspiration for the admin interface and e-commerce workflow
- **Statamic CMS**: Template system and content management approach
- **Laravel**: The amazing framework that makes this possible
- **Inertia.js**: Seamless SPA experience with server-side rendering

## 📞 Support

- **Issues**: [GitHub Issues](https://github.com/vitalijalbu/laravel-shopper/issues)
- **Discussions**: [GitHub Discussions](https://github.com/vitalijalbu/laravel-shopper/discussions)
- **Documentation**: [Full Documentation](https://laravel-shopper.dev)

---

**Made with ❤️ by [Vitali Jalbu](https://github.com/vitalijalbu)**

```bash
php artisan migrate
```

## 🧪 Testing the Package

### Option 1: Create a New Laravel App

```bash
# Create new Laravel app
laravel new shopper-test
cd shopper-test

# Install the package
composer config repositories.local path ../laravel-shopper
composer require vitalijalbu/laravel-shopper:@dev

# Publish and configure
php artisan vendor:publish --tag="shopper-config"
php artisan vendor:publish --tag="shopper-assets"

# Install frontend dependencies
npm install
npm run build


