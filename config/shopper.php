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

    /*
    |--------------------------------------------------------------------------
    | Fidelity/Loyalty System Configuration
    |--------------------------------------------------------------------------
    */
    'fidelity' => [
        'enabled' => env('SHOPPER_FIDELITY_ENABLED', true),
        
        // Card code configuration
        'card' => [
            'prefix' => env('SHOPPER_FIDELITY_CARD_PREFIX', 'FID'),
            'length' => env('SHOPPER_FIDELITY_CARD_LENGTH', 8), // Lunghezza del numero dopo il prefisso
            'separator' => env('SHOPPER_FIDELITY_CARD_SEPARATOR', '-'),
        ],
        
        // Points calculation system
        'points' => [
            'enabled' => env('SHOPPER_FIDELITY_POINTS_ENABLED', true),
            'currency_base' => env('SHOPPER_FIDELITY_CURRENCY_BASE', 'EUR'), // Valuta di base per il calcolo
            
            // Conversion rates: amount spent -> points earned
            'conversion_rules' => [
                // Scaglioni di conversione (spesa minima => punti per euro/dollaro)
                'tiers' => [
                    0 => 1,      // 0€+ = 1 punto per euro
                    100 => 1.5,  // 100€+ = 1.5 punti per euro  
                    500 => 2,    // 500€+ = 2 punti per euro
                    1000 => 3,   // 1000€+ = 3 punti per euro
                ],
            ],
            
            // Points expiration
            'expiration' => [
                'enabled' => env('SHOPPER_FIDELITY_POINTS_EXPIRATION', true),
                'months' => env('SHOPPER_FIDELITY_POINTS_EXPIRATION_MONTHS', 12), // Scadenza in mesi
            ],
            
            // Redemption rules
            'redemption' => [
                'min_points' => env('SHOPPER_FIDELITY_MIN_REDEMPTION_POINTS', 100),
                'points_to_currency_rate' => env('SHOPPER_FIDELITY_POINTS_TO_CURRENCY', 0.01), // 100 punti = 1€
            ],
        ],
    ],
];
