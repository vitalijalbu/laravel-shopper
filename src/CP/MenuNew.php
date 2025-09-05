<?php

namespace Shopper\CP;

use Illuminate\Support\Collection;

class Navigation
{
    protected static array $items = [];

    protected static array $sections = [];

    /**
     * Add navigation item
     */
    public static function item(string $name): NavigationItem
    {
        $item = new NavigationItem($name);
        static::$items[$name] = $item;

        return $item;
    }

    /**
     * Add navigation section
     */
    public static function section(string $name, ?string $label = null): NavigationSection
    {
        $section = new NavigationSection($name, $label ?? $name);
        static::$sections[$name] = $section;

        return $section;
    }

    /**
     * Get all navigation items
     */
    public static function items(): Collection
    {
        return collect(static::$items)
            ->filter(fn ($item) => $item->canView())
            ->sortBy('order')
            ->values();
    }

    /**
     * Get all navigation sections
     */
    public static function sections(): Collection
    {
        return collect(static::$sections)
            ->map(function ($section) {
                $section->items = $section->items()
                    ->filter(fn ($item) => $item->canView())
                    ->sortBy('order')
                    ->values();

                return $section;
            })
            ->filter(fn ($section) => $section->items->isNotEmpty())
            ->sortBy('order')
            ->values();
    }

    /**
     * Get navigation tree for frontend
     */
    public static function tree(): array
    {
        return [
            'sections' => static::sections(),
            'items' => static::items(),
        ];
    }

    /**
     * Register default Shopper navigation
     */
    public static function registerDefaults(): void
    {
        // Dashboard
        static::item('dashboard')
            ->label('Home')
            ->icon('home')
            ->url('/cp')
            ->order(1);

        // Orders Section
        static::section('orders', 'Orders')
            ->order(10);

        static::item('orders.index')
            ->label('Orders')
            ->icon('shopping-bag')
            ->url('/cp/orders')
            ->section('orders')
            ->order(1);

        static::item('orders.drafts')
            ->label('Drafts')
            ->url('/cp/orders/drafts')
            ->section('orders')
            ->order(2);

        static::item('orders.abandoned')
            ->label('Abandoned checkouts')
            ->url('/cp/abandoned-carts')
            ->section('orders')
            ->order(3);

        // Products Section
        static::section('products', 'Products')
            ->order(20);

        static::item('products.index')
            ->label('All products')
            ->icon('package')
            ->url('/cp/products')
            ->section('products')
            ->order(1);

        static::item('inventory')
            ->label('Inventory')
            ->url('/cp/inventory')
            ->section('products')
            ->order(2);

        static::item('collections.index')
            ->label('Collections')
            ->url('/cp/collections')
            ->section('products')
            ->order(3);

        static::item('product-types')
            ->label('Product types')
            ->url('/cp/product-types')
            ->section('products')
            ->order(4);

        static::item('gift-cards')
            ->label('Gift cards')
            ->url('/cp/gift-cards')
            ->section('products')
            ->order(5);

        // Customer Section
        static::section('customers', 'Customers')
            ->order(30);

        static::item('customers.index')
            ->label('Customers')
            ->icon('users')
            ->url('/cp/customers')
            ->section('customers')
            ->order(1);

        // Content Section
        static::section('content', 'Content')
            ->order(40);

        static::item('navigations')
            ->label('Navigation')
            ->icon('menu')
            ->url('/cp/navigations')
            ->section('content')
            ->order(1);

        static::item('blog.posts')
            ->label('Blog posts')
            ->url('/cp/blog/posts')
            ->section('content')
            ->order(2);

        static::item('reviews')
            ->label('Product reviews')
            ->url('/cp/reviews')
            ->section('content')
            ->order(3);

        // Analytics Section
        static::section('analytics', 'Analytics')
            ->order(50);

        static::item('analytics.overview')
            ->label('Overview')
            ->icon('trending-up')
            ->url('/cp/analytics')
            ->section('analytics')
            ->order(1);

        static::item('analytics.reports')
            ->label('Live view')
            ->url('/cp/analytics/live-view')
            ->section('analytics')
            ->order(2);

        // Marketing Section
        static::section('marketing', 'Marketing')
            ->order(60);

        static::item('discounts')
            ->label('Discounts')
            ->icon('percent')
            ->url('/cp/discounts')
            ->section('marketing')
            ->order(1);

        static::item('marketing.campaigns')
            ->label('Marketing')
            ->url('/cp/marketing')
            ->section('marketing')
            ->order(2);

        // Sales Channels Section
        static::section('sales-channels', 'Sales channels')
            ->order(70);

        static::item('online-store')
            ->label('Online Store')
            ->icon('globe')
            ->url('/cp/online-store')
            ->section('sales-channels')
            ->order(1);

        static::item('point-of-sale')
            ->label('Point of Sale')
            ->url('/cp/pos')
            ->section('sales-channels')
            ->order(2);

        // Apps Section
        static::section('apps', 'Apps')
            ->order(80);

        static::item('apps.store')
            ->label('App store')
            ->icon('grid')
            ->url('/cp/apps')
            ->section('apps')
            ->order(1);

        static::item('apps.installed')
            ->label('Installed apps')
            ->url('/cp/apps/installed')
            ->section('apps')
            ->order(2);

        // Settings Section
        static::section('settings', 'Settings')
            ->order(90);

        static::item('settings.general')
            ->label('General')
            ->icon('settings')
            ->url('/cp/settings/general')
            ->section('settings')
            ->order(1);

        static::item('settings.checkout')
            ->label('Checkout')
            ->url('/cp/settings/checkout')
            ->section('settings')
            ->order(2);

        static::item('settings.payment-gateways')
            ->label('Payments')
            ->url('/cp/settings/payment-gateways')
            ->section('settings')
            ->order(3);

        static::item('settings.shipping-methods')
            ->label('Shipping and delivery')
            ->url('/cp/settings/shipping-methods')
            ->section('settings')
            ->order(4);

        static::item('settings.tax-rates')
            ->label('Taxes')
            ->url('/cp/settings/tax-rates')
            ->section('settings')
            ->order(5);

        static::item('settings.email')
            ->label('Email')
            ->url('/cp/settings/email')
            ->section('settings')
            ->order(6);

        static::item('brands')
            ->label('Brands')
            ->url('/cp/brands')
            ->section('settings')
            ->order(7);

        static::item('utilities')
            ->label('Utilities')
            ->url('/cp/utilities')
            ->section('settings')
            ->order(8);
    }
}
