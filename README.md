# Laravel Shopper

🛒 **Laravel E-commerce Package with Statamic CMS Architecture**

A complete e-commerce package for Laravel inspired by Shopify, built with Vue.js 3, Inertia.js, and Tailwind CSS v4. Features collections, entries, fields, import/export, and multisite support just like Statamic CMS.

## ✨ Features

- **🛪 E-commerce Core**: Products, categories, brands, orders, customers
- **💝 Wishlists & Favorites**: Advanced wishlist system with public sharing + favorites for products/brands/categories
- **📝 Content Management**: Collections and entries system like Statamic
- **🎨 Modern Admin Interface**: Vue.js 3.5 + Inertia.js 2.0 + Tailwind CSS v4
- **📊 DataTable**: Advanced filtering, sorting, pagination, bulk operations
- **📈 Import/Export**: CSV import/export with League CSV
- **🌐 Multisite Support**: Multiple sites management
- **🔒 Authentication**: Laravel Sanctum integration
- **💰 Discount System**: Advanced coupon system (percentage, fixed, free shipping)
- **🧪 Testing**: PestPHP test suite included

## 📋 Requirements

- **PHP**: ^8.3
- **Laravel**: ^11.0
- **Node.js**: >=18.0.0
- **npm**: >=8.0.0

## 🚀 Installation

### 1. Install via Composer

```bash
composer require vitalijalbu/laravel-shopper
```

### 2. Publish Configuration & Assets

```bash
php artisan vendor:publish --tag="shopper-config"
php artisan vendor:publish --tag="shopper-views"
php artisan vendor:publish --tag="shopper-assets"
```

### 3. Install Frontend Dependencies

```bash
npm install
npm run build
```

### 4. Run Migrations

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

# Start server
php artisan serve
```

Then visit: **http://localhost:8000/cp**

### Option 2: Direct Testing

```bash
# Clone the package
git clone https://github.com/vitalijalbu/laravel-shopper.git
cd laravel-shopper

# Install dependencies
composer install
npm install

# Build assets
npm run build

# Run tests
composer test
```

## 🎯 Usage

### Admin Interface

Access the control panel at `/cp`:

```php
// Collections management
Route::get('/cp/collections')

// Entries management  
Route::get('/cp/collections/{collection}/entries')

// Import/Export utilities
Route::get('/cp/utilities/import')
Route::get('/cp/utilities/export')
```

### API Endpoints

```php
// Collections API
GET    /cp/api/collections
POST   /cp/api/collections
PUT    /cp/api/collections/{collection}
DELETE /cp/api/collections/{collection}

// Entries API
GET    /cp/api/collections/{collection}/entries
POST   /cp/api/collections/{collection}/entries
PUT    /cp/api/collections/{collection}/entries/{entry}
DELETE /cp/api/collections/{collection}/entries/{entry}
POST   /cp/api/collections/{collection}/entries/bulk
```

## 🏗️ Architecture

### Collections & Entries System

Inspired by Statamic CMS:

- **Collections**: Product catalogs, content types
- **Entries**: Individual items within collections  
- **Fields**: JSON schema-based field definitions
- **Sites**: Multisite architecture support

### Frontend Stack

- **Vue.js 3.5**: Composition API components
- **Inertia.js 2.0**: SPA without the complexity
- **Tailwind CSS v4**: Latest utility-first framework
- **Pinia**: State management (no Vue Router needed!)

## 📁 Key Files Structure

```
laravel-shopper/
├── src/
│   ├── Http/Controllers/Cp/        # Control Panel controllers
│   │   ├── CollectionsController.php
│   │   ├── EntriesController.php
│   │   └── ImportExportController.php
│   └── ShopperServiceProvider.php  # Main service provider
├── resources/
│   ├── js/                        # Vue.js components  
│   │   ├── components/            # Reusable components
│   │   ├── pages/                 # Inertia pages
│   │   └── app.js                 # Main app file
│   └── views/app.blade.php        # Main layout
├── routes/cp.php                  # All CP routes
├── config/shopper.php             # Configuration
└── tests/Feature/                 # PestPHP tests
```

## 🧪 Testing

```bash
# Run all tests
composer test

# Run with coverage  
composer test-coverage

# Code formatting
composer format
```

---

**🎉 Ready to use!** The package includes everything needed for a complete Statamic-style e-commerce admin interface.
- **Autenticazione**: Laravel Sanctum per API sicure
- **Design System**: Reka UI per un'interfaccia utente moderna
- **Database Ottimizzato**: Struttura database ispirata a LunarPHP
- **Estendibile**: Facilmente personalizzabile ed estendibile

## 📦 Installazione

### Requisiti

- PHP 8.2+
- Laravel 10.0+
- Node.js 18+

### Installazione via Composer

```bash
composer require vitalijalbu/laravel-shopper
```

### Pubblicazione degli Asset

```bash
# Pubblica la configurazione
php artisan vendor:publish --tag="shopper-config"

# Pubblica le migration
php artisan vendor:publish --tag="shopper-migrations"

# Pubblica le views (opzionale)
php artisan vendor:publish --tag="shopper-views"

# Pubblica gli asset Vue.js (opzionale)
php artisan vendor:publish --tag="shopper-assets"
```

### Esecuzione delle Migration

```bash
php artisan migrate
```

### Installazione Frontend (se usando asset personalizzati)

```bash
cd resources/js/vendor/shopper-admin
npm install
npm run build
```

## ⚙️ Configurazione

Il file di configurazione `config/shopper.php` contiene tutte le opzioni personalizzabili:

```php
return [
    'database' => [
        'table_prefix' => env('SHOPPER_DB_TABLE_PREFIX', 'shopper_'),
        'connection' => env('SHOPPER_DB_CONNECTION', 'mysql'),
    ],
    
    'admin' => [
        'enabled' => env('SHOPPER_ADMIN_ENABLED', true),
        'route_prefix' => env('SHOPPER_ADMIN_ROUTE_PREFIX', 'admin'),
        'middleware' => ['web', 'auth:sanctum'],
    ],
    
    'auth' => [
        'guard' => 'sanctum',
        'model' => 'App\Models\User',
    ],
    
    // ... altre configurazioni
];
```

### Variabili d'Ambiente

Aggiungi al tuo file `.env`:

```env
SHOPPER_DB_TABLE_PREFIX=shopper_
SHOPPER_ADMIN_ENABLED=true
SHOPPER_ADMIN_ROUTE_PREFIX=admin
SHOPPER_DEFAULT_CURRENCY=USD
SHOPPER_MEDIA_DISK=public
```

## 🏗️ Struttura del Package

```
laravel-shopper/
├── packages/
│   ├── core/                 # Package Core
│   │   ├── src/
│   │   │   ├── Models/       # Modelli Eloquent
│   │   │   └── ...
│   │   └── database/
│   │       └── migrations/   # Migration database
│   │
│   └── admin/                # Package Admin
│       ├── src/
│       │   ├── Http/
│       │   │   └── Controllers/
│       │   └── routes/
│       └── resources/
│           ├── js/           # Componenti Vue.js
│           │   ├── Pages/    # Pagine Inertia
│           │   └── Components/
│           └── css/          # Stili Tailwind CSS
│
├── src/                      # Service Provider principale
├── config/                   # File di configurazione
└── database/migrations/      # Migration pubblicabili
```

## 🛠️ Utilizzo

### Modelli Principali

```php
use VitaliJalbu\LaravelShopper\Core\Models\Product;
use VitaliJalbu\LaravelShopper\Core\Models\Customer;
use VitaliJalbu\LaravelShopper\Core\Models\Order;

// Creare un prodotto
$product = Product::create([
    'name' => 'Awesome T-Shirt',
    'slug' => 'awesome-t-shirt',
    'description' => 'A really awesome t-shirt',
    'status' => 'active',
    'brand_id' => 1,
    'product_type_id' => 1,
]);

// Aggiungere varianti
$product->variants()->create([
    'sku' => 'AWESOME-M-RED',
    'price' => 2999, // In centesimi
    'quantity' => 100,
    'option_values' => [
        'Size' => 'M',
        'Color' => 'Red'
    ]
]);
```

### Gestione Carrelli

```php
use VitaliJalbu\LaravelShopper\Core\Models\Cart;

$cart = Cart::create([
    'customer_id' => $customer->id,
    'currency_id' => 1,
    'channel_id' => 1,
]);

$cart->lines()->create([
    'purchasable_type' => ProductVariant::class,
    'purchasable_id' => $variant->id,
    'quantity' => 2,
    'unit_price' => $variant->price,
    'total_price' => $variant->price * 2,
]);
```

### API Routes

Il package espone automaticamente le API routes:

```
GET    /api/admin/products           # Lista prodotti
POST   /api/admin/products           # Crea prodotto
GET    /api/admin/products/{id}      # Dettaglio prodotto
PUT    /api/admin/products/{id}      # Aggiorna prodotto
DELETE /api/admin/products/{id}      # Elimina prodotto
```

## 🎨 Personalizzazione Frontend

### Usando Reka UI

I componenti sono costruiti con Reka UI per Vue 3:

```vue
<template>
  <Card>
    <CardHeader>
      <CardTitle>Product Details</CardTitle>
    </CardHeader>
    <CardContent>
      <Input 
        v-model="product.name" 
        label="Product Name"
        required
      />
    </CardContent>
  </Card>
</template>

<script setup>
import { Card, CardHeader, CardTitle, CardContent } from '@reka-ui/vue'
import Input from '@/Components/UI/Input.vue'
</script>
```

### Estendere il Layout

Puoi personalizzare il layout admin pubblicando le views:

```php
php artisan vendor:publish --tag="shopper-views"
```

## 🔐 Permessi e Sicurezza

Il package integra Spatie Permissions:

```php
// Assegnare permessi
$user->givePermissionTo('manage-products');
$user->givePermissionTo('manage-orders');

// Nei controller
if ($user->can('manage-products')) {
    // Logic here
}
```

### Middleware Disponibili

- `web` - Standard Laravel web middleware
- `auth:sanctum` - Autenticazione via Sanctum
- `shopper.admin` - Middleware custom per admin (se configurato)

## 📊 Database Schema

### Tabelle Principali

- `shopper_products` - Prodotti
- `shopper_product_variants` - Varianti prodotto
- `shopper_customers` - Clienti
- `shopper_orders` - Ordini
- `shopper_order_lines` - Righe ordine
- `shopper_carts` - Carrelli
- `shopper_cart_lines` - Righe carrello
- `shopper_brands` - Marchi
- `shopper_categories` - Categorie
- `shopper_discounts` - Sconti

### Relazioni

Il sistema di relazioni è ottimizzato per performance:

```php
// Prodotto con varianti e prezzi
$product = Product::with(['variants', 'brand', 'categories'])->find(1);

// Ordine completo
$order = Order::with(['lines.purchasable', 'customer', 'addresses'])->find(1);
```

## 🚀 Performance

### Caching

Il package supporta caching automatico:

```php
// Nel config
'cache' => [
    'enabled' => true,
    'prefix' => 'shopper:',
    'ttl' => 3600,
],
```

### Query Ottimizzate

Tutte le query sono ottimizzate con:
- Eager loading per ridurre N+1 problems
- Indici database appropriati
- Paginazione efficiente

## 🧪 Testing

```bash
vendor/bin/pest
```

## 🤝 Contribuire

1. Fork del repository
2. Crea un branch per la feature (`git checkout -b feature/AmazingFeature`)
3. Commit dei cambiamenti (`git commit -m 'Add some AmazingFeature'`)
4. Push al branch (`git push origin feature/AmazingFeature`)
5. Apri una Pull Request

## 📄 Licenza

Questo progetto è rilasciato sotto licenza MIT. Vedi il file `LICENSE` per dettagli.

## 🙏 Credits

- Ispirato da [LunarPHP](https://lunarphp.io) per la struttura database
- Costruito con [Laravel](https://laravel.com)
- UI con [Reka UI](https://reka-ui.com)
- Frontend con [Vue 3](https://vuejs.org) e [Inertia.js](https://inertiajs.com)

## 📞 Supporto

Per supporto, apri un issue su GitHub o contatta [vitalijalbu@example.com](mailto:vitalijalbu@example.com).
