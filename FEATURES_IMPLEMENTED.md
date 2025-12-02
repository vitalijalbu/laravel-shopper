# 🎉 Features Implementate - Laravel Shopper

Documentazione completa delle nuove funzionalità implementate per trasformare Laravel Shopper in una piattaforma eCommerce enterprise-grade.

---

## 📦 1. Addon System (Stile Statamic CMS)

Sistema completo di gestione addon modulari per estendere le funzionalità della piattaforma.

### Architettura

```
src/Core/Addon/
├── AddonInterface.php           # Interfaccia base
├── AbstractAddon.php            # Classe base astratta
├── AddonManager.php             # Gestione addon
├── AddonRepository.php          # Persistenza database
├── AddonServiceProvider.php     # Laravel service provider
├── Events/                      # Eventi lifecycle
│   ├── AddonInstalled.php
│   ├── AddonUninstalled.php
│   ├── AddonActivated.php
│   ├── AddonDeactivated.php
│   └── AddonUpdated.php
└── Exceptions/
    └── AddonException.php
```

### Features Principali

- ✅ **Lifecycle Completo**: install → activate → deactivate → uninstall → update
- ✅ **Dependency Management**: Gestione automatica dipendenze tra addon con version constraints
- ✅ **Semantic Versioning**: Supporto completo per versioning (^1.0.0, >=2.0.0, etc.)
- ✅ **Auto-discovery**: Caricamento automatico da directory `addons/` tramite manifest `addon.json`
- ✅ **Hooks System**:
  - Routes (web.php, api.php)
  - Views (blade templates)
  - Translations (multi-lingua)
  - Assets (public files)
  - Events & Listeners
  - Commands (Artisan)
  - Middleware
- ✅ **Event-Driven**: Eventi per ogni operazione (install, activate, etc.)
- ✅ **Config Schema**: Validazione configurazioni addon

### Utilizzo Base

```php
use Shopper\Core\Addon\AddonManager;

$addonManager = app(AddonManager::class);

// Installa un addon
$addonManager->install('payment-stripe');

// Attiva un addon
$addonManager->activate('payment-stripe');

// Disattiva un addon
$addonManager->deactivate('payment-stripe');

// Ottieni tutti gli addon attivi
$activeAddons = $addonManager->active();

// Ottieni un addon specifico
$addon = $addonManager->get('payment-stripe');
```

### Creare un Addon

**1. Struttura Directory:**

```
addons/payment-stripe/
├── addon.json              # Manifest
├── src/
│   └── PaymentStripeAddon.php
├── routes/
│   ├── web.php
│   └── api.php
├── resources/
│   ├── views/
│   └── lang/
├── database/
│   └── migrations/
├── public/
│   ├── css/
│   └── js/
└── composer.json
```

**2. Manifest (addon.json):**

```json
{
    "id": "payment-stripe",
    "name": "Stripe Payment Gateway",
    "description": "Accept payments via Stripe",
    "version": "1.0.0",
    "author": "Your Name",
    "class": "PaymentStripe\\PaymentStripeAddon",
    "dependencies": {
        "core-payment": ">=1.0.0"
    },
    "autoload": {
        "psr-4": {
            "PaymentStripe\\": "src/"
        }
    }
}
```

**3. Classe Addon:**

```php
<?php

namespace PaymentStripe;

use Shopper\Core\Addon\AbstractAddon;

class PaymentStripeAddon extends AbstractAddon
{
    public function getId(): string
    {
        return 'payment-stripe';
    }

    public function getName(): string
    {
        return 'Stripe Payment Gateway';
    }

    public function getVersion(): string
    {
        return '1.0.0';
    }

    public function getDependencies(): array
    {
        return [
            'core-payment' => '>=1.0.0',
        ];
    }

    public function getConfigSchema(): array
    {
        return [
            'api_key' => [
                'type' => 'string',
                'required' => true,
                'label' => 'Stripe API Key',
            ],
            'webhook_secret' => [
                'type' => 'string',
                'required' => true,
                'label' => 'Webhook Secret',
            ],
        ];
    }

    public function boot(): void
    {
        parent::boot();

        // Custom boot logic
    }

    public function register(): void
    {
        parent::register();

        // Bind services
        $this->app->singleton(StripeGateway::class);
    }

    protected function registerEvents(): void
    {
        Event::listen(OrderPlaced::class, ProcessStripePayment::class);
    }
}
```

---

## 🔌 2. GraphQL API (Lighthouse PHP)

API GraphQL completa per headless commerce con Lighthouse PHP.

### Schema Completo

**File**: `graphql/schema.graphql`

#### Query Disponibili

```graphql
type Query {
    # Products
    product(id: ID, handle: String): Product
    products(
        search: String
        category: ID
        brand: ID
        minPrice: Float
        maxPrice: Float
        inStock: Boolean
    ): [Product!]! @paginate

    # Collections
    collection(id: ID, handle: String): Collection
    collections: [Collection!]! @paginate

    # Customers (authenticated)
    customer(id: ID, email: String): Customer @guard
    customers(search: String): [Customer!]! @paginate @guard

    # Orders (authenticated)
    order(id: ID, number: String): Order @guard
    orders(customer: ID, status: OrderStatus): [Order!]! @paginate @guard

    # Current User
    me: Customer @auth(guard: "customers")
    myOrders: [Order!]! @paginate @guard(with: "customers")

    # Settings
    settings: Settings!
}
```

#### Mutations Disponibili

```graphql
type Mutation {
    # Authentication
    login(email: String!, password: String!): AuthPayload!
    register(input: RegisterInput!): AuthPayload!
    logout: LogoutPayload! @guard(with: "customers")

    # Profile Management
    updateProfile(input: UpdateProfileInput!): Customer! @guard
    changePassword(
        current_password: String!
        password: String!
        password_confirmation: String!
    ): Customer! @guard

    # Address Management
    createAddress(input: CreateAddressInput!): Address! @guard
    updateAddress(id: ID!, input: UpdateAddressInput!): Address! @guard
    deleteAddress(id: ID!): DeletePayload! @guard

    # Cart Operations
    addToCart(product_id: ID!, variant_id: ID, quantity: Int!): Cart!
    updateCartLine(line_id: ID!, quantity: Int!): Cart!
    removeFromCart(line_id: ID!): Cart!
    clearCart: Cart!
    applyCoupon(code: String!): Cart!
    removeCoupon: Cart!

    # Checkout
    createOrder(input: CreateOrderInput!): Order! @guard

    # Reviews
    createReview(input: CreateReviewInput!): Review! @guard
    updateReview(id: ID!, input: UpdateReviewInput!): Review! @guard
    deleteReview(id: ID!): DeletePayload! @guard
}
```

### Esempi di Utilizzo

**1. Query Prodotti con Filtri:**

```graphql
query GetProducts {
    products(
        search: "laptop"
        minPrice: 500
        maxPrice: 2000
        inStock: true
        first: 20
    ) {
        data {
            id
            name
            handle
            price
            compare_price
            in_stock
            on_sale
            discount_percentage
            images {
                url
            }
            category {
                name
                handle
            }
            brand {
                name
            }
            average_rating
            review_count
        }
        paginatorInfo {
            total
            currentPage
            lastPage
            hasMorePages
        }
    }
}
```

**2. Authentication:**

```graphql
# Login
mutation Login {
    login(email: "customer@example.com", password: "password") {
        access_token
        token_type
        expires_in
        customer {
            id
            full_name
            email
        }
    }
}

# Register
mutation Register {
    register(input: {
        first_name: "John"
        last_name: "Doe"
        email: "john@example.com"
        password: "password"
        password_confirmation: "password"
    }) {
        access_token
        customer {
            id
            full_name
        }
    }
}
```

**3. Cart Operations:**

```graphql
# Add to Cart
mutation AddToCart {
    addToCart(product_id: "123", quantity: 2) {
        lines {
            id
            product {
                name
                price
            }
            quantity
            total
        }
        subtotal
        tax
        shipping
        total
    }
}

# Apply Coupon
mutation ApplyCoupon {
    applyCoupon(code: "SAVE20") {
        lines { ... }
        discount
        total
        coupon_code
    }
}
```

**4. Create Order:**

```graphql
mutation CreateOrder {
    createOrder(input: {
        shipping_address_id: "456"
        billing_address_id: "456"
        shipping_method: "standard"
        payment_method: "stripe"
        customer_notes: "Please leave at door"
    }) {
        id
        number
        status
        total
        placed_at
    }
}
```

### GraphQL Playground

Accedi a GraphQL Playground su: `http://your-app.test/graphql-playground`

### Configurazione

**File**: `config/lighthouse.php`

```php
return [
    'route' => [
        'uri' => '/graphql',
        'middleware' => ['web'],
    ],

    'namespaces' => [
        'models' => ['Shopper\\Models'],
        'queries' => 'Shopper\\GraphQL\\Queries',
        'mutations' => 'Shopper\\GraphQL\\Mutations',
    ],

    'security' => [
        'max_query_complexity' => 1000,
        'max_query_depth' => 10,
    ],

    'pagination' => [
        'max_count' => 100,
    ],
];
```

---

## ⚙️ 3. Workflow Automation Engine

Sistema di automazione per creare workflow event-driven custom.

### Architettura

```
src/Workflows/
├── WorkflowInterface.php        # Interfaccia base
├── AbstractWorkflow.php         # Classe base
├── WorkflowManager.php          # Gestione ed esecuzione
├── DynamicWorkflow.php          # Workflow da database
├── Actions/                     # Azioni disponibili
│   ├── SendEmailAction.php
│   ├── UpdateProductAction.php
│   └── CreateNotificationAction.php
└── Events/
    ├── WorkflowExecuted.php
    └── WorkflowFailed.php
```

### Features Principali

- ✅ **Event-Driven**: Trigger basati su eventi Laravel
- ✅ **Conditions System**: Valutazione condizioni con operatori (=, !=, >, <, in, contains)
- ✅ **Actions System**: Azioni modulari ed estensibili
- ✅ **Priority System**: Ordinamento esecuzione per priorità
- ✅ **Workflow Logging**: Log completo di ogni esecuzione
- ✅ **Template Parsing**: Supporto variabili {{field.name}} per email/notifiche
- ✅ **Dynamic Workflows**: Creazione workflow via database senza codice

### Creare un Workflow

```php
<?php

namespace App\Workflows;

use Shopper\Workflows\AbstractWorkflow;
use Shopper\Events\ProductUpdated;

class LowStockAlertWorkflow extends AbstractWorkflow
{
    public function getId(): string
    {
        return 'low-stock-alert';
    }

    public function getName(): string
    {
        return 'Low Stock Alert';
    }

    public function getDescription(): string
    {
        return 'Send alert when product stock is low';
    }

    public function getTrigger(): string
    {
        return ProductUpdated::class;
    }

    public function getConditions(): array
    {
        return [
            [
                'field' => 'stock_quantity',
                'operator' => '<=',
                'value' => 10,
            ],
            [
                'field' => 'track_quantity',
                'operator' => '=',
                'value' => true,
            ],
        ];
    }

    public function getActions(): array
    {
        return [
            [
                'type' => 'send_email',
                'config' => [
                    'to' => 'admin@store.com',
                    'subject' => 'Low Stock Alert: {{product.name}}',
                    'body' => 'Product {{product.name}} (SKU: {{product.sku}}) has only {{product.stock_quantity}} items remaining.',
                ],
            ],
            [
                'type' => 'create_notification',
                'config' => [
                    'type' => 'warning',
                    'title' => 'Low Stock',
                    'message' => '{{product.name}} is running low on stock',
                ],
            ],
            [
                'type' => 'update_product',
                'config' => [
                    'updates' => [
                        'is_visible' => false, // Hide from storefront
                    ],
                ],
            ],
        ];
    }
}
```

### Registrare un Workflow

```php
use Shopper\Workflows\WorkflowManager;

$manager = app(WorkflowManager::class);
$manager->register(new LowStockAlertWorkflow());
```

### Operatori Disponibili

- `=` - Uguale
- `!=` - Diverso
- `>` - Maggiore
- `>=` - Maggiore o uguale
- `<` - Minore
- `<=` - Minore o uguale
- `in` - In array
- `not_in` - Non in array
- `contains` - Contiene stringa
- `not_contains` - Non contiene stringa

### Azioni Built-in

#### 1. SendEmailAction

```php
[
    'type' => 'send_email',
    'config' => [
        'to' => 'admin@store.com',
        'subject' => 'Order #{{order.number}} placed',
        'body' => 'Customer {{customer.full_name}} placed order #{{order.number}} for {{order.total_formatted}}',
    ],
]
```

#### 2. UpdateProductAction

```php
[
    'type' => 'update_product',
    'config' => [
        'updates' => [
            'is_featured' => true,
            'stock_quantity' => 0,
        ],
    ],
]
```

#### 3. CreateNotificationAction

```php
[
    'type' => 'create_notification',
    'config' => [
        'type' => 'info',  // info, success, warning, error
        'title' => 'New Order',
        'message' => 'Order #{{order.number}} received',
        'user_id' => 1,
    ],
]
```

### Workflow Dinamici (Database)

Crea workflow senza codice usando la tabella `workflows`:

```php
Workflow::create([
    'name' => 'Welcome Email',
    'trigger' => 'Shopper\\Events\\CustomerRegistered',
    'is_active' => true,
    'conditions' => [
        ['field' => 'email_verified', 'operator' => '=', 'value' => true],
    ],
    'actions' => [
        [
            'type' => 'send_email',
            'config' => [
                'to' => '{{customer.email}}',
                'subject' => 'Welcome to {{store.name}}!',
                'body' => 'Hi {{customer.first_name}}, welcome to our store!',
            ],
        ],
    ],
]);
```

---

## 💰 4. Advanced Pricing Engine

Sistema avanzato di pricing con regole multiple, sconti volume, customer group pricing e tiered pricing.

### Architettura

```
src/Pricing/
├── PricingEngine.php                    # Motore principale
├── PriceResult.php                      # Oggetto risultato
├── DynamicPricingRule.php               # Regole da database
└── Rules/
    ├── PricingRuleInterface.php
    ├── VolumeDiscountRule.php           # Sconti quantità
    └── CustomerGroupDiscountRule.php    # Sconti per gruppo
```

### Features Principali

- ✅ **Multiple Pricing Rules**: Stack di regole con priorità
- ✅ **Volume Discounts**: Sconti basati su quantità acquistata
- ✅ **Customer Group Pricing**: Prezzi differenziati per gruppo clienti
- ✅ **Tiered Pricing**: Fasce di prezzo progressive
- ✅ **Exclusive Rules**: Regole esclusive (solo una si applica)
- ✅ **Cumulative Discounts**: Sconti cumulativi
- ✅ **Context-Aware**: Pricing basato su contesto (location, tempo, etc.)
- ✅ **Dynamic Rules**: Regole configurabili da database

### Utilizzo Base

```php
use Shopper\Pricing\PricingEngine;

$pricingEngine = app(PricingEngine::class);

// Calcola prezzo per un cliente
$priceResult = $pricingEngine
    ->forCustomer($customer)
    ->withContext(['location' => 'US', 'channel' => 'web'])
    ->calculatePrice($product, $quantity = 10);

// Risultati
echo $priceResult->originalPrice;        // 100.00
echo $priceResult->finalPrice;           // 85.00
echo $priceResult->getDiscount();        // 15.00
echo $priceResult->getDiscountPercentage(); // 15%
echo $priceResult->getTotalPrice();      // 850.00 (85 * 10)

// Regole applicate
foreach ($priceResult->appliedRules as $rule) {
    echo $rule['rule'];          // "Volume Discount"
    echo $rule['adjustment'];    // ['type' => 'percentage', 'value' => 15]
    echo $rule['price_after'];   // 85.00
}
```

### Tiered Pricing

```php
$tiers = $pricingEngine->getTieredPricing($product);

// Risultato:
// [
//     ['min_quantity' => 1,   'max_quantity' => 9,   'price' => 100, 'discount_percentage' => 0],
//     ['min_quantity' => 10,  'max_quantity' => 49,  'price' => 95,  'discount_percentage' => 5],
//     ['min_quantity' => 50,  'max_quantity' => 99,  'price' => 90,  'discount_percentage' => 10],
//     ['min_quantity' => 100, 'max_quantity' => null, 'price' => 85,  'discount_percentage' => 15],
// ]
```

### Creare una Pricing Rule

```php
<?php

namespace App\Pricing\Rules;

use Shopper\Pricing\Rules\PricingRuleInterface;
use Shopper\Models\Customer;
use Shopper\Models\Product;

class BlackFridayDiscountRule implements PricingRuleInterface
{
    public function getName(): string
    {
        return 'Black Friday 30% Off';
    }

    public function getPriority(): int
    {
        return 100; // Alta priorità
    }

    public function isExclusive(): bool
    {
        return true; // Non combina con altre regole
    }

    public function appliesTo(
        Product $product,
        ?Customer $customer,
        int $quantity,
        array $context
    ): bool {
        // Solo per Black Friday
        $now = now();
        $blackFriday = now()->month(11)->day(24);

        return $now->isSameDay($blackFriday) &&
               $product->category_id === 5; // Solo categoria electronics
    }

    public function calculateAdjustment(
        float $currentPrice,
        Product $product,
        int $quantity,
        array $context
    ): ?array {
        return [
            'type' => 'percentage',
            'value' => 30,
        ];
    }
}
```

### Tipi di Adjustment

```php
// 1. Percentage Discount
['type' => 'percentage', 'value' => 20]  // -20%

// 2. Fixed Discount
['type' => 'fixed', 'value' => 10]       // -$10

// 3. Fixed Price
['type' => 'fixed_price', 'value' => 99] // Prezzo fisso $99

// 4. Multiply
['type' => 'multiply', 'value' => 0.8]   // Moltiplica per 0.8 (20% off)
```

### Volume Discount Built-in

```php
use Shopper\Pricing\Rules\VolumeDiscountRule;

// Configurazione automatica:
// 10-49 items:  5% off
// 50-99 items:  10% off
// 100+ items:   15% off

$priceResult = $pricingEngine->calculatePrice($product, 50);
// Applica automaticamente 10% di sconto
```

### Customer Group Discount Built-in

```php
use Shopper\Pricing\Rules\CustomerGroupDiscountRule;

// Configurazione automatica:
// Wholesale: 20% off
// VIP:       15% off
// Retail:    5% off

$customer->group->code = 'wholesale';
$priceResult = $pricingEngine
    ->forCustomer($customer)
    ->calculatePrice($product);
// Applica automaticamente 20% di sconto
```

### Dynamic Pricing Rules (Database)

```php
PricingRule::create([
    'name' => 'Summer Sale',
    'priority' => 50,
    'is_active' => true,
    'is_exclusive' => false,
    'conditions' => [
        ['field' => 'product.category_id', 'operator' => 'in', 'value' => [1, 2, 3]],
        ['field' => 'quantity', 'operator' => '>=', 'value' => 5],
    ],
    'adjustment' => [
        'type' => 'percentage',
        'value' => 15,
    ],
]);
```

---

## 🎨 Storefront Blade Components

Ho anche creato componenti Blade riutilizzabili per il storefront:

### Form Components

```blade
<!-- Input -->
<x-storefront.form.input
    name="email"
    type="email"
    placeholder="your@email.com"
    :required="true"
/>

<!-- Textarea -->
<x-storefront.form.textarea
    name="notes"
    :rows="4"
    placeholder="Your notes here..."
/>

<!-- Select -->
<x-storefront.form.select name="country" :required="true">
    <option value="US">United States</option>
    <option value="IT">Italy</option>
</x-storefront.form.select>

<!-- Checkbox -->
<x-storefront.form.checkbox
    name="accept_terms"
    :checked="true"
/>

<!-- Label -->
<x-storefront.form.label for="email" :required="true">
    Email Address
</x-storefront.form.label>

<!-- Button -->
<x-storefront.form.button
    type="submit"
    variant="primary"
    size="lg"
    :loading="false"
>
    Place Order
</x-storefront.form.button>
```

### Button Variants

- `primary` - Indigo (default)
- `secondary` - Gray
- `danger` - Red
- `ghost` - Transparent

### Button Sizes

- `sm` - Small
- `md` - Medium (default)
- `lg` - Large

---

## 📊 Database Migrations

```bash
# Crea tabella addons
php artisan migrate

# Tables create:
# - addons (id, name, version, is_active, config, timestamps)
# - workflows (id, name, trigger, conditions, actions, is_active, timestamps)
# - workflow_logs (id, workflow_id, trigger, data, result, executed_at)
# - pricing_rules (id, name, type, priority, conditions, adjustment, is_active, timestamps)
```

---

## 🚀 Quick Start

### 1. Installa Lighthouse PHP

```bash
composer require nuwave/lighthouse
php artisan vendor:publish --tag=lighthouse-schema
```

### 2. Registra Service Providers

```php
// config/app.php
'providers' => [
    // ...
    Shopper\Core\Addon\AddonServiceProvider::class,
    Shopper\Workflows\WorkflowServiceProvider::class,
    Shopper\Pricing\PricingServiceProvider::class,
],
```

### 3. Pubblica Configurazioni

```bash
php artisan vendor:publish --provider="Shopper\Core\Addon\AddonServiceProvider"
php artisan vendor:publish --tag=lighthouse-config
```

### 4. Esegui Migrations

```bash
php artisan migrate
```

### 5. Crea il Primo Addon

```bash
mkdir -p addons/my-first-addon/src
# Crea addon.json e classe addon
```

---

## 📚 Risorse Aggiuntive

- **Lighthouse PHP Docs**: https://lighthouse-php.com/
- **Statamic Addons Guide**: https://statamic.dev/extending/addons
- **Laravel Events**: https://laravel.com/docs/events
- **Semantic Versioning**: https://semver.org/

---

## ✅ Checklist Implementazione

- [x] Addon System completo
- [x] GraphQL API con Lighthouse
- [x] Workflow Automation Engine
- [x] Advanced Pricing Engine
- [x] Blade Form Components per Storefront
- [x] Database Migrations
- [x] Eventi per ogni feature
- [x] Logging system
- [x] Documentazione completa

---

**Data Implementazione**: Dicembre 2025
**Versione**: 1.0.0
**Autore**: Claude Code + Vitali

🎉 **Tutte le feature enterprise sono ora implementate!**
