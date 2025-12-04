<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Performance Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure various performance optimization settings
    | for Laravel Shopper to ensure optimal performance in production.
    |
    */

    'cache' => [
        /*
        |--------------------------------------------------------------------------
        | Cache Settings
        |--------------------------------------------------------------------------
        |
        | Configure cache TTL and tags for different data types
        |
        */
        'enabled' => env('CARTINO_CACHE_ENABLED', true),

        'ttl' => [
            'products' => env('CARTINO_CACHE_PRODUCTS_TTL', 3600), // 1 hour
            'categories' => env('CARTINO_CACHE_CATEGORIES_TTL', 7200), // 2 hours
            'brands' => env('CARTINO_CACHE_BRANDS_TTL', 7200), // 2 hours
            'collections' => env('CARTINO_CACHE_COLLECTIONS_TTL', 3600), // 1 hour
            'users' => env('CARTINO_CACHE_USERS_TTL', 1800), // 30 minutes
            'settings' => env('CARTINO_CACHE_SETTINGS_TTL', 86400), // 24 hours
            'navigation' => env('CARTINO_CACHE_NAVIGATION_TTL', 3600), // 1 hour
            'stats' => env('CARTINO_CACHE_STATS_TTL', 600), // 10 minutes
        ],

        'tags' => [
            'products' => 'shopper_products',
            'categories' => 'shopper_categories',
            'brands' => 'shopper_brands',
            'collections' => 'shopper_collections',
            'users' => 'shopper_users',
            'settings' => 'shopper_settings',
            'navigation' => 'shopper_navigation',
            'stats' => 'shopper_stats',
        ],

        // Cache warming settings
        'warm_up' => [
            'enabled' => env('CARTINO_CACHE_WARMUP_ENABLED', true),
            'schedule' => env('CARTINO_CACHE_WARMUP_SCHEDULE', '0 */6 * * *'), // Every 6 hours
            'items' => [
                'categories',
                'brands',
                'featured_products',
                'navigation',
            ],
        ],
    ],

    'database' => [
        /*
        |--------------------------------------------------------------------------
        | Database Optimization
        |--------------------------------------------------------------------------
        |
        | Settings for database query optimization
        |
        */
        'eager_loading' => [
            'enabled' => env('CARTINO_EAGER_LOADING_ENABLED', true),
            'default_relations' => [
                'products' => ['category', 'brand'],
                'orders' => ['customer', 'items.product'],
                'categories' => ['parent', 'children'],
            ],
        ],

        'query_log' => [
            'enabled' => env('CARTINO_QUERY_LOG_ENABLED', false),
            'slow_query_threshold' => env('CARTINO_SLOW_QUERY_THRESHOLD', 1000), // milliseconds
        ],

        'chunking' => [
            'enabled' => env('CARTINO_CHUNKING_ENABLED', true),
            'size' => env('CARTINO_CHUNK_SIZE', 1000),
        ],
    ],

    'search' => [
        /*
        |--------------------------------------------------------------------------
        | Search Configuration
        |--------------------------------------------------------------------------
        |
        | Settings for search functionality and indexing
        |
        */
        'driver' => env('CARTINO_SEARCH_DRIVER', 'database'), // database, elasticsearch, algolia

        'elasticsearch' => [
            'hosts' => env('ELASTICSEARCH_HOSTS', 'localhost:9200'),
            'index_prefix' => env('ELASTICSEARCH_INDEX_PREFIX', 'shopper_'),
            'settings' => [
                'number_of_shards' => 1,
                'number_of_replicas' => 0,
            ],
        ],

        'algolia' => [
            'app_id' => env('ALGOLIA_APP_ID', ''),
            'secret' => env('ALGOLIA_SECRET', ''),
            'search_key' => env('ALGOLIA_SEARCH_KEY', ''),
        ],

        'indexing' => [
            'queue' => env('CARTINO_SEARCH_QUEUE', 'indexing'),
            'chunk_size' => env('CARTINO_SEARCH_CHUNK_SIZE', 100),
            'timeout' => env('CARTINO_SEARCH_TIMEOUT', 30),
        ],
    ],

    'queues' => [
        /*
        |--------------------------------------------------------------------------
        | Queue Configuration
        |--------------------------------------------------------------------------
        |
        | Configure different queues for different types of jobs
        |
        */
        'default' => env('CARTINO_QUEUE_DEFAULT', 'default'),

        'queues' => [
            'orders' => env('CARTINO_QUEUE_ORDERS', 'orders'),
            'indexing' => env('CARTINO_QUEUE_INDEXING', 'indexing'),
            'webhooks' => env('CARTINO_QUEUE_WEBHOOKS', 'webhooks'),
            'notifications' => env('CARTINO_QUEUE_NOTIFICATIONS', 'notifications'),
            'analytics' => env('CARTINO_QUEUE_ANALYTICS', 'analytics'),
            'images' => env('CARTINO_QUEUE_IMAGES', 'images'),
            'exports' => env('CARTINO_QUEUE_EXPORTS', 'exports'),
        ],

        'retry_after' => env('CARTINO_QUEUE_RETRY_AFTER', 90),
        'max_tries' => env('CARTINO_QUEUE_MAX_TRIES', 3),
        'timeout' => env('CARTINO_QUEUE_TIMEOUT', 60),
    ],

    'api' => [
        /*
        |--------------------------------------------------------------------------
        | API Performance Settings
        |--------------------------------------------------------------------------
        |
        | Rate limiting and performance settings for API endpoints
        |
        */
        'rate_limiting' => [
            'enabled' => env('CARTINO_API_RATE_LIMITING_ENABLED', true),
            'per_minute' => env('CARTINO_API_RATE_LIMIT', 60),
            'per_hour' => env('CARTINO_API_RATE_LIMIT_HOURLY', 1000),
        ],

        'pagination' => [
            'default_per_page' => env('CARTINO_API_PER_PAGE', 20),
            'max_per_page' => env('CARTINO_API_MAX_PER_PAGE', 100),
        ],

        'response_caching' => [
            'enabled' => env('CARTINO_API_RESPONSE_CACHING', true),
            'ttl' => env('CARTINO_API_CACHE_TTL', 300), // 5 minutes
        ],

        'compression' => [
            'enabled' => env('CARTINO_API_COMPRESSION', true),
            'level' => env('CARTINO_API_COMPRESSION_LEVEL', 6),
        ],
    ],

    'images' => [
        /*
        |--------------------------------------------------------------------------
        | Image Optimization
        |--------------------------------------------------------------------------
        |
        | Settings for image processing and optimization
        |
        */
        'optimization' => [
            'enabled' => env('CARTINO_IMAGE_OPTIMIZATION', true),
            'quality' => env('CARTINO_IMAGE_QUALITY', 85),
            'progressive' => env('CARTINO_IMAGE_PROGRESSIVE', true),
        ],

        'formats' => [
            'webp' => env('CARTINO_IMAGE_WEBP', true),
            'avif' => env('CARTINO_IMAGE_AVIF', false),
        ],

        'sizes' => [
            'thumbnail' => [150, 150],
            'small' => [300, 300],
            'medium' => [600, 600],
            'large' => [1200, 1200],
        ],

        'lazy_loading' => env('CARTINO_IMAGE_LAZY_LOADING', true),
    ],

    'monitoring' => [
        /*
        |--------------------------------------------------------------------------
        | Performance Monitoring
        |--------------------------------------------------------------------------
        |
        | Settings for performance monitoring and alerting
        |
        */
        'enabled' => env('CARTINO_MONITORING_ENABLED', true),

        'metrics' => [
            'response_time' => true,
            'memory_usage' => true,
            'query_count' => true,
            'cache_hit_rate' => true,
        ],

        'alerts' => [
            'slow_queries' => [
                'enabled' => env('CARTINO_ALERT_SLOW_QUERIES', true),
                'threshold' => env('CARTINO_ALERT_SLOW_QUERY_THRESHOLD', 2000), // milliseconds
            ],
            'high_memory' => [
                'enabled' => env('CARTINO_ALERT_HIGH_MEMORY', true),
                'threshold' => env('CARTINO_ALERT_MEMORY_THRESHOLD', 128), // MB
            ],
            'queue_backlog' => [
                'enabled' => env('CARTINO_ALERT_QUEUE_BACKLOG', true),
                'threshold' => env('CARTINO_ALERT_QUEUE_THRESHOLD', 1000), // jobs
            ],
        ],
    ],

    'security' => [
        /*
        |--------------------------------------------------------------------------
        | Security & Performance
        |--------------------------------------------------------------------------
        |
        | Security settings that also affect performance
        |
        */
        'csrf_protection' => env('CARTINO_CSRF_PROTECTION', true),
        'xss_protection' => env('CARTINO_XSS_PROTECTION', true),
        'sql_injection_protection' => env('CARTINO_SQL_INJECTION_PROTECTION', true),

        'input_validation' => [
            'strict_mode' => env('CARTINO_STRICT_VALIDATION', true),
            'sanitize_input' => env('CARTINO_SANITIZE_INPUT', true),
            'max_request_size' => env('CARTINO_MAX_REQUEST_SIZE', 10240), // KB
        ],
    ],

    'cdn' => [
        /*
        |--------------------------------------------------------------------------
        | CDN Configuration
        |--------------------------------------------------------------------------
        |
        | Configure CDN settings for static assets
        |
        */
        'enabled' => env('CARTINO_CDN_ENABLED', false),
        'url' => env('CARTINO_CDN_URL', ''),
        'pull_zone' => env('CARTINO_CDN_PULL_ZONE', ''),

        'assets' => [
            'css' => env('CARTINO_CDN_CSS', true),
            'js' => env('CARTINO_CDN_JS', true),
            'images' => env('CARTINO_CDN_IMAGES', true),
            'fonts' => env('CARTINO_CDN_FONTS', true),
        ],
    ],
];
