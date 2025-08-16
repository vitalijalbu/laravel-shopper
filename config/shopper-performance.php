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
        'enabled' => env('SHOPPER_CACHE_ENABLED', true),

        'ttl' => [
            'products' => env('SHOPPER_CACHE_PRODUCTS_TTL', 3600), // 1 hour
            'categories' => env('SHOPPER_CACHE_CATEGORIES_TTL', 7200), // 2 hours
            'brands' => env('SHOPPER_CACHE_BRANDS_TTL', 7200), // 2 hours
            'collections' => env('SHOPPER_CACHE_COLLECTIONS_TTL', 3600), // 1 hour
            'users' => env('SHOPPER_CACHE_USERS_TTL', 1800), // 30 minutes
            'settings' => env('SHOPPER_CACHE_SETTINGS_TTL', 86400), // 24 hours
            'navigation' => env('SHOPPER_CACHE_NAVIGATION_TTL', 3600), // 1 hour
            'stats' => env('SHOPPER_CACHE_STATS_TTL', 600), // 10 minutes
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
            'enabled' => env('SHOPPER_CACHE_WARMUP_ENABLED', true),
            'schedule' => env('SHOPPER_CACHE_WARMUP_SCHEDULE', '0 */6 * * *'), // Every 6 hours
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
            'enabled' => env('SHOPPER_EAGER_LOADING_ENABLED', true),
            'default_relations' => [
                'products' => ['category', 'brand'],
                'orders' => ['customer', 'items.product'],
                'categories' => ['parent', 'children'],
            ],
        ],

        'query_log' => [
            'enabled' => env('SHOPPER_QUERY_LOG_ENABLED', false),
            'slow_query_threshold' => env('SHOPPER_SLOW_QUERY_THRESHOLD', 1000), // milliseconds
        ],

        'chunking' => [
            'enabled' => env('SHOPPER_CHUNKING_ENABLED', true),
            'size' => env('SHOPPER_CHUNK_SIZE', 1000),
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
        'driver' => env('SHOPPER_SEARCH_DRIVER', 'database'), // database, elasticsearch, algolia

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
            'queue' => env('SHOPPER_SEARCH_QUEUE', 'indexing'),
            'chunk_size' => env('SHOPPER_SEARCH_CHUNK_SIZE', 100),
            'timeout' => env('SHOPPER_SEARCH_TIMEOUT', 30),
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
        'default' => env('SHOPPER_QUEUE_DEFAULT', 'default'),

        'queues' => [
            'orders' => env('SHOPPER_QUEUE_ORDERS', 'orders'),
            'indexing' => env('SHOPPER_QUEUE_INDEXING', 'indexing'),
            'webhooks' => env('SHOPPER_QUEUE_WEBHOOKS', 'webhooks'),
            'notifications' => env('SHOPPER_QUEUE_NOTIFICATIONS', 'notifications'),
            'analytics' => env('SHOPPER_QUEUE_ANALYTICS', 'analytics'),
            'images' => env('SHOPPER_QUEUE_IMAGES', 'images'),
            'exports' => env('SHOPPER_QUEUE_EXPORTS', 'exports'),
        ],

        'retry_after' => env('SHOPPER_QUEUE_RETRY_AFTER', 90),
        'max_tries' => env('SHOPPER_QUEUE_MAX_TRIES', 3),
        'timeout' => env('SHOPPER_QUEUE_TIMEOUT', 60),
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
            'enabled' => env('SHOPPER_API_RATE_LIMITING_ENABLED', true),
            'per_minute' => env('SHOPPER_API_RATE_LIMIT', 60),
            'per_hour' => env('SHOPPER_API_RATE_LIMIT_HOURLY', 1000),
        ],

        'pagination' => [
            'default_per_page' => env('SHOPPER_API_PER_PAGE', 20),
            'max_per_page' => env('SHOPPER_API_MAX_PER_PAGE', 100),
        ],

        'response_caching' => [
            'enabled' => env('SHOPPER_API_RESPONSE_CACHING', true),
            'ttl' => env('SHOPPER_API_CACHE_TTL', 300), // 5 minutes
        ],

        'compression' => [
            'enabled' => env('SHOPPER_API_COMPRESSION', true),
            'level' => env('SHOPPER_API_COMPRESSION_LEVEL', 6),
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
            'enabled' => env('SHOPPER_IMAGE_OPTIMIZATION', true),
            'quality' => env('SHOPPER_IMAGE_QUALITY', 85),
            'progressive' => env('SHOPPER_IMAGE_PROGRESSIVE', true),
        ],

        'formats' => [
            'webp' => env('SHOPPER_IMAGE_WEBP', true),
            'avif' => env('SHOPPER_IMAGE_AVIF', false),
        ],

        'sizes' => [
            'thumbnail' => [150, 150],
            'small' => [300, 300],
            'medium' => [600, 600],
            'large' => [1200, 1200],
        ],

        'lazy_loading' => env('SHOPPER_IMAGE_LAZY_LOADING', true),
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
        'enabled' => env('SHOPPER_MONITORING_ENABLED', true),

        'metrics' => [
            'response_time' => true,
            'memory_usage' => true,
            'query_count' => true,
            'cache_hit_rate' => true,
        ],

        'alerts' => [
            'slow_queries' => [
                'enabled' => env('SHOPPER_ALERT_SLOW_QUERIES', true),
                'threshold' => env('SHOPPER_ALERT_SLOW_QUERY_THRESHOLD', 2000), // milliseconds
            ],
            'high_memory' => [
                'enabled' => env('SHOPPER_ALERT_HIGH_MEMORY', true),
                'threshold' => env('SHOPPER_ALERT_MEMORY_THRESHOLD', 128), // MB
            ],
            'queue_backlog' => [
                'enabled' => env('SHOPPER_ALERT_QUEUE_BACKLOG', true),
                'threshold' => env('SHOPPER_ALERT_QUEUE_THRESHOLD', 1000), // jobs
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
        'csrf_protection' => env('SHOPPER_CSRF_PROTECTION', true),
        'xss_protection' => env('SHOPPER_XSS_PROTECTION', true),
        'sql_injection_protection' => env('SHOPPER_SQL_INJECTION_PROTECTION', true),

        'input_validation' => [
            'strict_mode' => env('SHOPPER_STRICT_VALIDATION', true),
            'sanitize_input' => env('SHOPPER_SANITIZE_INPUT', true),
            'max_request_size' => env('SHOPPER_MAX_REQUEST_SIZE', 10240), // KB
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
        'enabled' => env('SHOPPER_CDN_ENABLED', false),
        'url' => env('SHOPPER_CDN_URL', ''),
        'pull_zone' => env('SHOPPER_CDN_PULL_ZONE', ''),

        'assets' => [
            'css' => env('SHOPPER_CDN_CSS', true),
            'js' => env('SHOPPER_CDN_JS', true),
            'images' => env('SHOPPER_CDN_IMAGES', true),
            'fonts' => env('SHOPPER_CDN_FONTS', true),
        ],
    ],
];
