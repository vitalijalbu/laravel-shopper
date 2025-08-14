<?php

namespace VitaliJalbu\LaravelShopper\Core;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Schema\Blueprint;
use VitaliJalbu\LaravelShopper\Core\Models\Brand;
use VitaliJalbu\LaravelShopper\Core\Models\Cart;
use VitaliJalbu\LaravelShopper\Core\Models\CartAddress;
use VitaliJalbu\LaravelShopper\Core\Models\CartLine;
use VitaliJalbu\LaravelShopper\Core\Models\Category;
use VitaliJalbu\LaravelShopper\Core\Models\Channel;
use VitaliJalbu\LaravelShopper\Core\Models\Collection;
use VitaliJalbu\LaravelShopper\Core\Models\Country;
use VitaliJalbu\LaravelShopper\Core\Models\Currency;
use VitaliJalbu\LaravelShopper\Core\Models\Customer;
use VitaliJalbu\LaravelShopper\Core\Models\CustomerGroup;
use VitaliJalbu\LaravelShopper\Core\Models\Discount;
use VitaliJalbu\LaravelShopper\Core\Models\Order;
use VitaliJalbu\LaravelShopper\Core\Models\OrderAddress;
use VitaliJalbu\LaravelShopper\Core\Models\OrderLine;
use VitaliJalbu\LaravelShopper\Core\Models\Product;
use VitaliJalbu\LaravelShopper\Core\Models\ProductOption;
use VitaliJalbu\LaravelShopper\Core\Models\ProductOptionValue;
use VitaliJalbu\LaravelShopper\Core\Models\ProductType;
use VitaliJalbu\LaravelShopper\Core\Models\ProductVariant;
use VitaliJalbu\LaravelShopper\Core\Models\ShippingMethod;
use VitaliJalbu\LaravelShopper\Core\Models\ShippingZone;
use VitaliJalbu\LaravelShopper\Core\Models\TaxClass;
use VitaliJalbu\LaravelShopper\Core\Models\TaxRate;
use VitaliJalbu\LaravelShopper\Core\Models\Transaction;

class ShopperCoreServiceProvider extends ServiceProvider
{
    protected array $modelBindings = [
        'Brand' => Brand::class,
        'Cart' => Cart::class,
        'CartAddress' => CartAddress::class,
        'CartLine' => CartLine::class,
        'Category' => Category::class,
        'Channel' => Channel::class,
        'Collection' => Collection::class,
        'Country' => Country::class,
        'Currency' => Currency::class,
        'Customer' => Customer::class,
        'CustomerGroup' => CustomerGroup::class,
        'Discount' => Discount::class,
        'Order' => Order::class,
        'OrderAddress' => OrderAddress::class,
        'OrderLine' => OrderLine::class,
        'Product' => Product::class,
        'ProductOption' => ProductOption::class,
        'ProductOptionValue' => ProductOptionValue::class,
        'ProductType' => ProductType::class,
        'ProductVariant' => ProductVariant::class,
        'ShippingMethod' => ShippingMethod::class,
        'ShippingZone' => ShippingZone::class,
        'TaxClass' => TaxClass::class,
        'TaxRate' => TaxRate::class,
        'Transaction' => Transaction::class,
    ];

    public function register(): void
    {
        foreach ($this->modelBindings as $key => $class) {
            $this->app->bind("shopper.models.{$key}", $class);
        }

        $this->registerMacros();
    }

    public function boot(): void
    {
        if (! config('shopper.database.disable_migrations', false)) {
            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        }

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'shopper');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ], 'shopper-core-migrations');

            $this->publishes([
                __DIR__ . '/../resources/lang' => lang_path('vendor/shopper'),
            ], 'shopper-core-lang');
        }
    }

    protected function registerMacros(): void
    {
        Blueprint::macro('userForeignKey', function (bool $nullable = false) {
            $userModel = config('shopper.auth.model', 'App\Models\User');
            $userTable = (new $userModel)->getTable();
            $userKeyName = (new $userModel)->getKeyName();
            
            $column = $this->unsignedBigInteger('user_id');
            
            if ($nullable) {
                $column->nullable();
            }
            
            return $column->index();
        });

        Blueprint::macro('money', function (string $column, string $currency = 'currency') {
            $this->unsignedBigInteger($column)->comment('Price in cents');
            $this->string($currency, 3)->default('USD');
        });

        Blueprint::macro('dimensions', function () {
            $this->decimal('length', 10, 2)->nullable();
            $this->decimal('width', 10, 2)->nullable();
            $this->decimal('height', 10, 2)->nullable();
            $this->decimal('weight', 10, 3)->nullable();
            $this->string('dimension_unit', 10)->default('cm');
            $this->string('weight_unit', 10)->default('kg');
        });

        Blueprint::macro('scheduling', function () {
            $this->dateTime('starts_at')->nullable()->index();
            $this->dateTime('ends_at')->nullable()->index();
        });
    }
}
