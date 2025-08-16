<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Database Settings
    |--------------------------------------------------------------------------
    */
    'database' => [
        'table_prefix' => env('SHOPPER_DB_TABLE_PREFIX', 'shopper_'),
        'connection' => env('SHOPPER_DB_CONNECTION', 'mysql'),
        'disable_migrations' => env('SHOPPER_DISABLE_MIGRATIONS', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Settings
    |--------------------------------------------------------------------------
    */
    'auth' => [
        'guard' => 'sanctum',
        'model' => 'App\Models\User',
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Settings
    |--------------------------------------------------------------------------
    */
    'admin' => [
        'route_prefix' => 'admin',
        'middleware' => ['web', 'auth', 'verified'],
        'auth_required' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Control Panel Settings
    |--------------------------------------------------------------------------
    */
    'cp' => [
        'name' => env('SHOPPER_CP_NAME', 'Control Panel'),
        'route_prefix' => env('SHOPPER_CP_PREFIX', 'cp'),
        'url' => env('SHOPPER_CP_URL', '/cp'),

        'branding' => [
            'logo' => env('SHOPPER_CP_LOGO'),
            'logo_dark' => env('SHOPPER_CP_LOGO_DARK'),
            'favicon' => env('SHOPPER_CP_FAVICON'),
        ],

        'pagination' => [
            'per_page' => 25,
        ],

        'date_format' => 'Y-m-d H:i:s',
        'timezone' => env('APP_TIMEZONE', 'UTC'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Locales
    |--------------------------------------------------------------------------
    */
    'locales' => [
        'it' => 'Italiano',
        'en' => 'English',
    ],

    'default_locale' => 'it',

    /*
    |--------------------------------------------------------------------------
    | Admin Settings
    |--------------------------------------------------------------------------
    */
    'admin' => [
        'enabled' => env('SHOPPER_ADMIN_ENABLED', true),
        'route_prefix' => env('SHOPPER_ADMIN_ROUTE_PREFIX', 'admin'),
        'middleware' => ['web', 'auth:sanctum'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Storefront Settings
    |--------------------------------------------------------------------------
    */
    'storefront' => [
        'enabled' => env('SHOPPER_STOREFRONT_ENABLED', true),
        'route_prefix' => env('SHOPPER_STOREFRONT_ROUTE_PREFIX', 'shop'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Status Configuration
    |--------------------------------------------------------------------------
    */
    'orders' => [
        'statuses' => [
            'pending' => [
                'label' => 'Pending',
                'color' => '#f59e0b',
            ],
            'processing' => [
                'label' => 'Processing',
                'color' => '#3b82f6',
            ],
            'shipped' => [
                'label' => 'Shipped',
                'color' => '#10b981',
            ],
            'delivered' => [
                'label' => 'Delivered',
                'color' => '#059669',
            ],
            'cancelled' => [
                'label' => 'Cancelled',
                'color' => '#ef4444',
            ],
            'refunded' => [
                'label' => 'Refunded',
                'color' => '#6b7280',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Product Configuration
    |--------------------------------------------------------------------------
    */
    'products' => [
        'statuses' => [
            'active' => [
                'label' => 'Active',
                'color' => '#10b981',
            ],
            'draft' => [
                'label' => 'Draft',
                'color' => '#6b7280',
            ],
            'archived' => [
                'label' => 'Archived',
                'color' => '#ef4444',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Currency Settings
    |--------------------------------------------------------------------------
    */
    'currency' => [
        'default' => env('SHOPPER_DEFAULT_CURRENCY', 'USD'),
        'supported' => ['USD', 'EUR', 'GBP', 'JPY'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Storage Settings
    |--------------------------------------------------------------------------
    */
    'media' => [
        'disk' => env('SHOPPER_MEDIA_DISK', 'public'),
        'path' => env('SHOPPER_MEDIA_PATH', 'shopper'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Customer Groups
    |--------------------------------------------------------------------------
    */
    'customer_groups' => [
        'default' => env('SHOPPER_DEFAULT_CUSTOMER_GROUP', 'retail'),
    ],
];
