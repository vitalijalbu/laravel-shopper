# 🎨 Storefront Theme System - Guida Completa

## 📋 Overview

Cartino include un **sistema di temi completamente esportabile** per lo storefront con:
- ✅ Blade components riutilizzabili
- ✅ Tailwind CSS 4.0
- ✅ Multi-lingua (IT/EN) con traduzioni complete
- ✅ Alpine.js per interattività
- ✅ SEO ottimizzato
- ✅ Responsive design
- ✅ Accessibilità (WCAG 2.1)
- ✅ **Export/Import temi**
- ✅ **REST API + Blade rendering**

---

## 🏗️ Struttura Tema

```
resources/
├── views/
│   ├── themes/
│   │   └── default/                    # Tema base
│   │       ├── layouts/
│   │       │   └── app.blade.php      # Layout principale
│   │       ├── pages/
│   │       │   ├── home.blade.php
│   │       │   ├── product-index.blade.php
│   │       │   ├── product-show.blade.php
│   │       │   ├── collection-index.blade.php
│   │       │   ├── collection-show.blade.php
│   │       │   ├── cart.blade.php
│   │       │   ├── checkout.blade.php
│   │       │   └── account/
│   │       │       ├── dashboard.blade.php
│   │       │       ├── orders.blade.php
│   │       │       └── order-show.blade.php
│   │       └── partials/
│   │           ├── product-grid.blade.php
│   │           ├── filters.blade.php
│   │           └── breadcrumbs.blade.php
│   │
│   └── components/
│       └── storefront/                 # Blade Components
│           ├── header.blade.php
│           ├── footer.blade.php
│           ├── product-card.blade.php
│           ├── cart-sidebar.blade.php
│           ├── mobile-menu.blade.php
│           ├── notification.blade.php
│           └── ...
│
├── lang/
│   ├── en/
│   │   └── storefront.php             # Traduzioni EN
│   └── it/
│       └── storefront.php             # Traduzioni IT
│
└── css/
    └── storefront.css                  # Styles Tailwind

```

---

## 🎨 Componenti Blade Creati

### 1. **Layout Base** (`layouts/app.blade.php`)

```blade
@extends('themes.default.layouts.app')

@section('content')
    <!-- Your page content -->
@endsection
```

**Features:**
- SEO meta tags completi (OG, Twitter Card)
- Multi-lingua
- Alpine.js integration
- Vite asset bundling
- Flash messages
- Cart sidebar off-canvas
- Mobile menu

### 2. **Header Component** (`x-storefront::header`)

```blade
<x-storefront::header :transparent="false" />
```

**Features:**
- Logo dinamico da settings
- Mega menu con dropdown
- Search bar espandibile
- Cart icon con badge count
- Account dropdown (authenticated)
- Mobile responsive hamburger menu
- Sticky header con scroll effect
- Language switcher
- Announcement bar (optional)

**Props:**
- `transparent` (boolean): Header trasparente su hero sections

### 3. **Footer Component** (`x-storefront::footer`)

```blade
<x-storefront::footer />
```

**Features:**
- Company info con social links
- Quick links (4 colonne)
- Newsletter signup form
- Payment methods icons
- Language switcher
- Copyright info

### 4. **Product Card** (`x-storefront::product-card`)

```blade
<x-storefront::product-card :product="$product" :lazy="true" />
```

**Features:**
- Product image con lazy loading
- Badges (Featured, Discount, Low Stock, Out of Stock)
- Quick view button (hover)
- Wishlist button
- Star rating
- Price con compare-at-price
- Quick add to cart
- Responsive design

**Props:**
- `product` (Product model): Required
- `lazy` (boolean): Lazy load images (default: true)

### 5. **Cart Sidebar** (`x-storefront::cart-sidebar`)

```blade
<x-storefront::cart-sidebar />
```

Off-canvas cart con Alpine.js:
- Slide da destra
- Items list con quantity controls
- Subtotal/Tax/Total
- Remove items
- Proceed to checkout button

### 6. **Mobile Menu** (`x-storefront::mobile-menu`)

```blade
<x-storefront::mobile-menu />
```

Full-screen mobile navigation:
- Animated slide-in
- Nested menus (accordion)
- Account links
- Language switcher

### 7. **Notification Component** (`x-storefront::notification`)

```blade
<x-storefront::notification type="success" message="Product added!" />
```

Toast notifications (auto-dismiss):
- Success, Error, Warning, Info
- Auto-close dopo 5 secondi
- Animated slide-in/out

---

## 🌍 Multi-Lingua

### Traduzioni Disponibili

**Lingue supportate:**
- 🇬🇧 English (`en`)
- 🇮🇹 Italiano (`it`)

**File traduzioni:**
- `resources/lang/en/storefront.php`
- `resources/lang/it/storefront.php`

### Utilizzo

```blade
<!-- In Blade -->
{{ __('storefront.product.add_to_cart') }}
{{ __('storefront.cart.title') }}
{{ __('storefront.nav.home') }}

<!-- Con parametri -->
{{ __('storefront.product.low_stock', ['count' => 5]) }}
```

### Categorie Traduzioni

1. **Navigation** (`nav.*`)
   - home, shop, products, collections, etc.

2. **Product** (`product.*`)
   - price, add_to_cart, in_stock, features, etc.

3. **Cart** (`cart.*`)
   - title, checkout, subtotal, shipping, etc.

4. **Checkout** (`checkout.*`)
   - billing_address, payment_method, etc.

5. **Account** (`account.*`)
   - dashboard, orders, wishlist, etc.

6. **Search** (`search.*`)
   - placeholder, results, no_results, etc.

7. **Filters** (`filters.*`)
   - categories, brands, sort_by, etc.

8. **Footer** (`footer.*`)
   - about_us, newsletter, privacy_policy, etc.

9. **Messages** (`messages.*`)
   - success, error, item_added, etc.

10. **Common** (`common.*`)
    - view_all, save, cancel, loading, etc.

### Switch Lingua

```php
// Route per cambiare lingua
Route::get('/locale/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'it'])) {
        session(['locale' => $locale]);
        app()->setLocale($locale);
    }
    return redirect()->back();
})->name('locale.switch');
```

---

## 🎨 Tailwind CSS Configuration

### Palette Colori Default

```css
/* Primary */
--color-primary-50: #eef2ff;
--color-primary-500: #6366f1;  /* Indigo */
--color-primary-600: #4f46e5;
--color-primary-700: #4338ca;

/* Secondary */
--color-secondary-50: #f9fafb;
--color-secondary-500: #6b7280;
--color-secondary-900: #111827;
```

### Customizzazione

```js
// tailwind.config.js
export default {
  theme: {
    extend: {
      colors: {
        primary: {
          50: '#your-color',
          // ...
        }
      },
      fontFamily: {
        sans: ['Inter', 'sans-serif'],
      },
    },
  },
}
```

---

## 📦 Export/Import Temi

### Export Tema

```php
// src/Services/ThemeExporter.php
namespace Shopper\Services;

class ThemeExporter
{
    public function export(string $themeName): string
    {
        $themePath = resource_path("views/themes/{$themeName}");
        $exportPath = storage_path("app/themes/{$themeName}.zip");

        $zip = new \ZipArchive();
        $zip->open($exportPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        // Add theme files
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($themePath),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($themePath) + 1);
                $zip->addFile($filePath, $relativePath);
            }
        }

        // Add theme manifest
        $manifest = [
            'name' => $themeName,
            'version' => '1.0.0',
            'author' => 'Your Name',
            'description' => 'Theme description',
            'screenshot' => 'screenshot.png',
            'requires' => [
                'laravel-shopper' => '^2.0',
                'tailwindcss' => '^4.0'
            ],
            'assets' => [
                'css' => ['storefront.css'],
                'js' => ['storefront.js']
            ]
        ];

        $zip->addFromString('theme.json', json_encode($manifest, JSON_PRETTY_PRINT));

        $zip->close();

        return $exportPath;
    }
}
```

### Import Tema

```php
// src/Services/ThemeImporter.php
namespace Shopper\Services;

class ThemeImporter
{
    public function import(string $zipPath): bool
    {
        $zip = new \ZipArchive();

        if ($zip->open($zipPath) !== true) {
            throw new \Exception('Failed to open theme package');
        }

        // Read manifest
        $manifestContent = $zip->getFromName('theme.json');
        $manifest = json_decode($manifestContent, true);

        $themeName = $manifest['name'];
        $extractPath = resource_path("views/themes/{$themeName}");

        // Extract theme
        $zip->extractTo($extractPath);
        $zip->close();

        // Install assets
        $this->installAssets($themeName, $manifest);

        return true;
    }

    protected function installAssets(string $themeName, array $manifest): void
    {
        // Copy CSS to public
        if (!empty($manifest['assets']['css'])) {
            foreach ($manifest['assets']['css'] as $css) {
                $source = resource_path("views/themes/{$themeName}/assets/{$css}");
                $dest = public_path("themes/{$themeName}/{$css}");

                if (file_exists($source)) {
                    copy($source, $dest);
                }
            }
        }

        // Compile assets with Vite
        // ...
    }
}
```

### CLI Commands

```bash
# Export tema
php artisan shopper:theme:export default

# Import tema
php artisan shopper:theme:import /path/to/theme.zip

# Attiva tema
php artisan shopper:theme:activate default

# Lista temi installati
php artisan shopper:theme:list
```

---

## 🔧 Customizzazione Avanzata

### Override Components

Per customizzare un component senza modificare il tema base:

```blade
<!-- app/View/Components/Storefront/ProductCard.php -->
namespace App\View\Components\Storefront;

class ProductCard extends \Shopper\View\Components\Storefront\ProductCard
{
    public function render()
    {
        return view('components.storefront.custom-product-card');
    }
}
```

### Theme Hooks

```php
// In your theme's functions.php or ServiceProvider

// Before header
add_action('storefront.header.before', function() {
    echo '<div class="promo-banner">Free shipping on orders over $50!</div>';
});

// After product info
add_action('storefront.product.after_info', function($product) {
    echo '<div class="product-badges">Eco-friendly</div>';
});

// Footer credits
add_filter('storefront.footer.credits', function($credits) {
    return $credits . ' | Designed by Your Company';
});
```

---

## 🎯 Best Practices

### 1. **Performance**
- ✅ Lazy load images
- ✅ Minimize CSS/JS (Vite)
- ✅ Use CDN per assets
- ✅ Cache Blade views
- ✅ Optimize database queries (N+1)

### 2. **SEO**
- ✅ Semantic HTML
- ✅ Meta tags completi
- ✅ Schema.org markup
- ✅ Sitemap.xml generato
- ✅ robots.txt ottimizzato

### 3. **Accessibilità**
- ✅ ARIA labels
- ✅ Keyboard navigation
- ✅ Focus visible
- ✅ Alt text per immagini
- ✅ Color contrast WCAG AA

### 4. **Mobile First**
- ✅ Responsive grid
- ✅ Touch-friendly buttons (min 44x44px)
- ✅ Swipe gestures
- ✅ Viewport meta tag

---

## 🚀 Deployment

### Production Checklist

```bash
# 1. Optimize assets
npm run build

# 2. Cache views
php artisan view:cache

# 3. Cache routes
php artisan route:cache

# 4. Cache config
php artisan config:cache

# 5. Optimize autoloader
composer install --optimize-autoloader --no-dev

# 6. Enable OPcache
# php.ini: opcache.enable=1

# 7. CDN per assets
# Configure in config/filesystems.php
```

---

## 📚 Esempi Completi

### Home Page

```blade
@extends('themes.default.layouts.app')

@section('content')
    <!-- Hero Section -->
    <section class="relative h-[600px] bg-gray-900">
        <img src="/hero.jpg" class="absolute inset-0 h-full w-full object-cover opacity-50" />
        <div class="relative mx-auto max-w-7xl px-4 h-full flex items-center">
            <div class="max-w-2xl text-white">
                <h1 class="text-5xl font-bold mb-4">{{ __('storefront.hero.title') }}</h1>
                <p class="text-xl mb-8">{{ __('storefront.hero.subtitle') }}</p>
                <a href="{{ route('storefront.products.index') }}" class="btn-primary">
                    {{ __('storefront.common.shop_now') }}
                </a>
            </div>
        </div>
    </section>

    <!-- Featured Products -->
    <section class="py-16">
        <div class="mx-auto max-w-7xl px-4">
            <h2 class="text-3xl font-bold mb-8">{{ __('storefront.home.featured_products') }}</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($featuredProducts as $product)
                    <x-storefront::product-card :product="$product" />
                @endforeach
            </div>
        </div>
    </section>
@endsection
```

### Product Page

```blade
@extends('themes.default.layouts.app')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8">
        <!-- Breadcrumbs -->
        <x-storefront::breadcrumbs :items="$breadcrumbs" />

        <div class="mt-8 grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Product Gallery -->
            <div>
                <x-storefront::product-gallery :product="$product" />
            </div>

            <!-- Product Info -->
            <div>
                <h1 class="text-3xl font-bold">{{ $product->name }}</h1>

                <!-- Rating -->
                @if($product->average_rating > 0)
                    <x-storefront::rating :value="$product->average_rating" :count="$product->review_count" />
                @endif

                <!-- Price -->
                <div class="mt-4">
                    <span class="text-3xl font-bold">{{ money($product->price) }}</span>
                    @if($product->compare_price)
                        <span class="text-xl text-gray-500 line-through ml-2">
                            {{ money($product->compare_price) }}
                        </span>
                    @endif
                </div>

                <!-- Description -->
                <div class="mt-6 prose">
                    {!! $product->description !!}
                </div>

                <!-- Add to Cart Form -->
                <form action="{{ route('storefront.cart.add') }}" method="POST" class="mt-8">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">

                    <!-- Variants -->
                    @if($product->variants->count() > 0)
                        <x-storefront::variant-selector :product="$product" />
                    @endif

                    <!-- Quantity -->
                    <div class="mt-4">
                        <label class="block text-sm font-medium mb-2">
                            {{ __('storefront.product.quantity') }}
                        </label>
                        <input
                            type="number"
                            name="quantity"
                            value="1"
                            min="1"
                            class="w-24 rounded-md border-gray-300"
                        >
                    </div>

                    <!-- Add to Cart Button -->
                    <button
                        type="submit"
                        class="mt-6 w-full btn-primary"
                        {{ $product->stock_quantity <= 0 ? 'disabled' : '' }}
                    >
                        @if($product->stock_quantity > 0)
                            {{ __('storefront.product.add_to_cart') }}
                        @else
                            {{ __('storefront.product.out_of_stock') }}
                        @endif
                    </button>
                </form>
            </div>
        </div>

        <!-- Related Products -->
        @if($relatedProducts->count() > 0)
            <section class="mt-16">
                <h2 class="text-2xl font-bold mb-6">{{ __('storefront.product.related_products') }}</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach($relatedProducts as $related)
                        <x-storefront::product-card :product="$related" />
                    @endforeach
                </div>
            </section>
        @endif
    </div>
@endsection
```

---

## 🎨 Theme Development Workflow

1. **Setup locale**
   ```bash
   npm install
   npm run dev
   ```

2. **Watch per modifiche**
   ```bash
   npm run dev
   # + php artisan serve
   ```

3. **Test multi-lingua**
   - Cambia lingua da footer
   - Verifica tutte le pagine

4. **Test responsive**
   - Mobile (320px)
   - Tablet (768px)
   - Desktop (1280px)

5. **Build production**
   ```bash
   npm run build
   ```

6. **Export tema**
   ```bash
   php artisan shopper:theme:export default --with-assets
   ```

---

## 📖 Resources

- [Tailwind CSS Documentation](https://tailwindcss.com)
- [Alpine.js Documentation](https://alpinejs.dev)
- [Laravel Blade Components](https://laravel.com/docs/blade#components)
- [WCAG 2.1 Guidelines](https://www.w3.org/WAI/WCAG21/quickref/)

---

🎉 **Tema completo pronto per l'uso!**
