<?php

declare(strict_types=1);

namespace Cartino\Services;

use Cartino\Enums\AddressType;
use Cartino\Enums\AppInstallationStatus;
use Cartino\Enums\AppStatus;
use Cartino\Enums\AttributeType;
use Cartino\Enums\CartStatus;
use Cartino\Enums\CurrencyLanguageEnums\CurrencyStatus;
use Cartino\Enums\CurrencyLanguageEnums\CurrencyType;
use Cartino\Enums\CurrencyLanguageEnums\RegulatoryStatus;
use Cartino\Enums\DiscountTargetType;
use Cartino\Enums\DiscountType;
use Cartino\Enums\FulfillmentStatus;
use Cartino\Enums\Gender;
use Cartino\Enums\InventoryLocationType;
use Cartino\Enums\MenuItemType;
use Cartino\Enums\OrderStatus;
use Cartino\Enums\PaymentStatus;
use Cartino\Enums\ProductEnums\PricingRuleType;
use Cartino\Enums\ProductEnums\ReturnReason;
use Cartino\Enums\ProductEnums\ReturnStatus;
use Cartino\Enums\ProductRelationType;
use Cartino\Enums\ProductType;
use Cartino\Enums\PurchaseOrderStatus;
use Cartino\Enums\ShippingCalculationMethod;
use Cartino\Enums\ShippingMethodType;
use Cartino\Enums\Status;
use Cartino\Enums\StockMovementType;
use Cartino\Enums\StockReservationStatus;
use Cartino\Enums\StockStatus;
use Cartino\Enums\StockTransferStatus;
use Cartino\Enums\SupplierStatus;
use Cartino\Enums\TransactionStatus;
use Cartino\Enums\TransactionType;
use Cartino\Enums\WishlistStatus;
use Cartino\Support\EnumRegistry;
use Illuminate\Support\Facades\Cache;

/**
 * Class EnumService
 *
 * Centralized service for managing enums and their translations.
 * Provides methods to retrieve enums with extensions for use in Inertia/Vue.
 */
class EnumService
{
    /**
     * List of all enum classes in the system.
     *
     * @var array<string, class-string>
     */
    protected array $enums = [
        'status' => Status::class,
        'orderStatus' => OrderStatus::class,
        'paymentStatus' => PaymentStatus::class,
        'fulfillmentStatus' => FulfillmentStatus::class,
        'cartStatus' => CartStatus::class,
        'stockStatus' => StockStatus::class,
        'productType' => ProductType::class,
        'discountType' => DiscountType::class,
        'discountTargetType' => DiscountTargetType::class,
        'stockMovementType' => StockMovementType::class,
        'stockReservationStatus' => StockReservationStatus::class,
        'stockTransferStatus' => StockTransferStatus::class,
        'shippingCalculationMethod' => ShippingCalculationMethod::class,
        'shippingMethodType' => ShippingMethodType::class,
        'supplierStatus' => SupplierStatus::class,
        'purchaseOrderStatus' => PurchaseOrderStatus::class,
        'transactionStatus' => TransactionStatus::class,
        'transactionType' => TransactionType::class,
        'wishlistStatus' => WishlistStatus::class,
        'menuItemType' => MenuItemType::class,
        'attributeType' => AttributeType::class,
        'productRelationType' => ProductRelationType::class,
        'gender' => Gender::class,
        'inventoryLocationType' => InventoryLocationType::class,
        'addressType' => AddressType::class,
        'appStatus' => AppStatus::class,
        'appInstallationStatus' => AppInstallationStatus::class,
        'pricingRuleType' => PricingRuleType::class,
        'returnStatus' => ReturnStatus::class,
        'returnReason' => ReturnReason::class,
        'currencyType' => CurrencyType::class,
        'currencyStatus' => CurrencyStatus::class,
        'regulatoryStatus' => RegulatoryStatus::class,
    ];

    /**
     * Cache duration in seconds (1 hour).
     */
    protected int $cacheDuration = 3600;

    /**
     * Get all enums with their options (including extensions).
     *
     * @param string|null $locale Optional locale
     * @return array<string, array>
     */
    public function all(?string $locale = null): array
    {
        $locale = $locale ?? app()->getLocale();
        $cacheKey = "enums.all.{$locale}";

        return Cache::remember($cacheKey, $this->cacheDuration, function () use ($locale) {
            $result = [];

            foreach ($this->enums as $key => $enumClass) {
                $result[$key] = EnumRegistry::toSelectOptions($enumClass, $locale);
            }

            return $result;
        });
    }

    /**
     * Get a specific enum with its options (including extensions).
     *
     * @param string $key The enum key
     * @param string|null $locale Optional locale
     * @return array
     */
    public function get(string $key, ?string $locale = null): array
    {
        if (! isset($this->enums[$key])) {
            throw new \InvalidArgumentException("Enum '{$key}' not found");
        }

        $locale = $locale ?? app()->getLocale();
        $enumClass = $this->enums[$key];

        return EnumRegistry::toSelectOptions($enumClass, $locale);
    }

    /**
     * Get simple key-value pairs for a specific enum.
     *
     * @param string $key The enum key
     * @param string|null $locale Optional locale
     * @return array<string, string>
     */
    public function options(string $key, ?string $locale = null): array
    {
        if (! isset($this->enums[$key])) {
            throw new \InvalidArgumentException("Enum '{$key}' not found");
        }

        $locale = $locale ?? app()->getLocale();
        $enumClass = $this->enums[$key];

        return EnumRegistry::translatedOptions($enumClass, $locale);
    }

    /**
     * Get all translations for all enums in all locales.
     *
     * @param array|null $locales Optional list of locales
     * @return array<string, array<string, array>>
     */
    public function allTranslations(?array $locales = null): array
    {
        $locales = $locales ?? config('cartino.locales', ['en', 'it']);
        $result = [];

        foreach ($this->enums as $key => $enumClass) {
            $result[$key] = EnumRegistry::allTranslations($enumClass, $locales);
        }

        return $result;
    }

    /**
     * Get enums for Inertia props (optimized for frontend).
     *
     * @param array|null $only Optional list of enum keys to include
     * @param string|null $locale Optional locale
     * @return array
     */
    public function forInertia(?array $only = null, ?string $locale = null): array
    {
        $locale = $locale ?? app()->getLocale();
        $enums = $only ? array_intersect_key($this->enums, array_flip($only)) : $this->enums;
        $result = [];

        foreach ($enums as $key => $enumClass) {
            $result[$key] = EnumRegistry::toSelectOptions($enumClass, $locale);
        }

        return $result;
    }

    /**
     * Clear enum cache.
     *
     * @return void
     */
    public function clearCache(): void
    {
        $locales = config('cartino.locales', ['en', 'it']);

        foreach ($locales as $locale) {
            Cache::forget("enums.all.{$locale}");
        }
    }

    /**
     * Register a new enum in the service.
     *
     * @param string $key The enum key
     * @param class-string $enumClass The enum class
     * @return void
     */
    public function register(string $key, string $enumClass): void
    {
        $this->enums[$key] = $enumClass;
        $this->clearCache();
    }

    /**
     * Get all registered enum keys.
     *
     * @return array<string>
     */
    public function keys(): array
    {
        return array_keys($this->enums);
    }

    /**
     * Get the class name for an enum key.
     *
     * @param string $key
     * @return class-string|null
     */
    public function getClass(string $key): ?string
    {
        return $this->enums[$key] ?? null;
    }
}
