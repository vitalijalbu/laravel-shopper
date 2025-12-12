<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Database Settings
    |--------------------------------------------------------------------------
    */
    'database' => [
        'table_prefix' => env('CARTINO_DB_TABLE_PREFIX', 'shopper_'),
        'connection' => env('CARTINO_DB_CONNECTION', 'mysql'),
        'disable_migrations' => env('CARTINO_DISABLE_MIGRATIONS', false),
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
        'name' => env('APP_NAME', 'Cartino'),
        'route_prefix' => env('CARTINO_CP_PREFIX', 'cp'),
        'url' => env('CARTINO_CP_URL', '/cp'),

        'branding' => [
            'logo' => env('CARTINO_CP_LOGO'),
            'logo_dark' => env('CARTINO_CP_LOGO_DARK'),
            'favicon' => env('CARTINO_CP_FAVICON'),
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
        'enabled' => env('CARTINO_ADMIN_ENABLED', true),
        'route_prefix' => env('CARTINO_ADMIN_ROUTE_PREFIX', 'admin'),
        'middleware' => ['web', 'auth:sanctum'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Storefront Settings
    |--------------------------------------------------------------------------
    */
    'storefront' => [
        'enabled' => env('CARTINO_STOREFRONT_ENABLED', true),
        'route_prefix' => env('CARTINO_STOREFRONT_ROUTE_PREFIX', 'shop'),
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
        'default' => env('CARTINO_DEFAULT_CURRENCY', 'USD'),
        'supported' => ['USD', 'EUR', 'GBP', 'JPY'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Storage Settings
    |--------------------------------------------------------------------------
    */
    'media' => [
        'disk' => env('CARTINO_MEDIA_DISK', 'public'),
        'path' => env('CARTINO_MEDIA_PATH', 'cartino'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Customer Groups
    |--------------------------------------------------------------------------
    */
    'customer_groups' => [
        'default' => env('CARTINO_DEFAULT_CUSTOMER_GROUP', 'retail'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Fidelity/Loyalty System Configuration
    |--------------------------------------------------------------------------
    */
    'fidelity' => [
        'enabled' => env('CARTINO_FIDELITY_ENABLED', true),

        // Card code configuration
        'card' => [
            'prefix' => env('CARTINO_FIDELITY_CARD_PREFIX', 'FID'),
            'length' => env('CARTINO_FIDELITY_CARD_LENGTH', 8), // Lunghezza del numero dopo il prefisso
            'separator' => env('CARTINO_FIDELITY_CARD_SEPARATOR', '-'),
        ],

        // Points calculation system
        'points' => [
            'enabled' => env('CARTINO_FIDELITY_POINTS_ENABLED', true),
            'currency_base' => env('CARTINO_FIDELITY_CURRENCY_BASE', 'EUR'), // Valuta di base per il calcolo

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
                'enabled' => env('CARTINO_FIDELITY_POINTS_EXPIRATION', true),
                'months' => env('CARTINO_FIDELITY_POINTS_EXPIRATION_MONTHS', 12), // Scadenza in mesi
            ],

            // Redemption rules
            'redemption' => [
                'min_points' => env('CARTINO_FIDELITY_MIN_REDEMPTION_POINTS', 100),
                'points_to_currency_rate' => env('CARTINO_FIDELITY_POINTS_TO_CURRENCY', 0.01), // 100 punti = 1€
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Advanced Filters Configuration
    |--------------------------------------------------------------------------
    | Sistema di filtri unificato simile a Directus per tutte le API
    */
    'filters' => [
        /*
        |--------------------------------------------------------------------------
        | Pagination Configuration
        |--------------------------------------------------------------------------
        */
        'pagination' => [
            'default' => env('CARTINO_PAGINATION_DEFAULT', 15),
            'max' => env('CARTINO_PAGINATION_MAX', 100),
            'options' => [10, 15, 25, 50, 100],
        ],

        /*
        |--------------------------------------------------------------------------
        | Cache Configuration
        |--------------------------------------------------------------------------
        */
        'cache' => [
            'enabled' => env('CARTINO_FILTERS_CACHE_ENABLED', true),
            'ttl' => env('CARTINO_FILTERS_CACHE_TTL', 3600), // 1 hour
            'tags' => ['shopper_filters', 'products', 'orders'],
        ],

        /*
        |--------------------------------------------------------------------------
        | Operators Configuration
        |--------------------------------------------------------------------------
        */
        'operators' => [
            'enabled' => [
                'eq', 'ne', 'gt', 'gte', 'lt', 'lte',
                'like', 'nlike', 'starts', 'ends',
                'in', 'nin', 'between', 'nbetween',
                'null', 'nnull', 'date', 'month', 'year',
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | Model Specific Settings
        |--------------------------------------------------------------------------
        */
        'models' => [
            'product' => [
                'per_page' => 20,
                'max_per_page' => 100,
                'default_sort' => '-created_at',
                'searchable' => ['name', 'description', 'short_description', 'sku'],
                'filterable' => [
                    'price', 'compare_price', 'cost_price', 'stock_quantity',
                    'brand_id', 'product_type_id', 'status', 'is_featured',
                    'is_physical', 'is_digital', 'requires_shipping',
                    'track_quantity', 'allow_out_of_stock_purchases',
                    'stock_status', 'weight', 'published_at',
                ],
                'sortable' => [
                    'name', 'price', 'compare_price', 'cost_price',
                    'stock_quantity', 'weight', 'is_featured', 'status',
                    'published_at', 'created_at', 'updated_at',
                    'average_rating', 'review_count',
                ],
            ],

            'order' => [
                'per_page' => 25,
                'max_per_page' => 50,
                'default_sort' => '-created_at',
                'searchable' => ['order_number', 'customer_email'],
                'filterable' => [
                    'status', 'payment_status', 'fulfillment_status',
                    'total', 'subtotal', 'tax_total', 'shipping_total',
                    'customer_id', 'currency_id', 'shipped_at', 'delivered_at',
                ],
                'sortable' => [
                    'order_number', 'total', 'subtotal', 'status',
                    'payment_status', 'fulfillment_status', 'created_at',
                    'shipped_at', 'delivered_at',
                ],
            ],

            'customer' => [
                'per_page' => 30,
                'max_per_page' => 100,
                'default_sort' => '-created_at',
                'searchable' => ['first_name', 'last_name', 'email', 'phone'],
                'filterable' => [
                    'is_enabled', 'gender', 'date_of_birth',
                    'email_verified_at', 'last_login_at',
                ],
                'sortable' => [
                    'first_name', 'last_name', 'email', 'created_at',
                    'last_login_at', 'is_enabled',
                ],
            ],

            'brand' => [
                'per_page' => 20,
                'searchable' => ['name', 'description'],
                'filterable' => ['is_enabled', 'created_at'],
                'sortable' => ['name', 'created_at', 'is_enabled'],
            ],

            'collection' => [
                'per_page' => 20,
                'searchable' => ['name', 'description'],
                'filterable' => ['type', 'is_enabled', 'created_at'],
                'sortable' => ['name', 'type', 'created_at', 'is_enabled'],
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | Rate Limiting
        |--------------------------------------------------------------------------
        */
        'rate_limit' => [
            'enabled' => env('CARTINO_FILTERS_RATE_LIMIT', true),
            'max_requests' => 60,
            'per_minutes' => 1,
        ],

        /*
        |--------------------------------------------------------------------------
        | Performance Optimization
        |--------------------------------------------------------------------------
        */
        'performance' => [
            'auto_index_hints' => env('CARTINO_FILTERS_AUTO_INDEX', true),
            'query_timeout' => env('CARTINO_FILTERS_QUERY_TIMEOUT', 30),
            'max_joins' => env('CARTINO_FILTERS_MAX_JOINS', 5),
        ],
    ],
];
