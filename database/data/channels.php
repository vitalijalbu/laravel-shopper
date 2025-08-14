<?php

return [
    [
        'name' => 'Default Store',
        'slug' => 'default',
        'description' => 'Main e-commerce store',
        'url' => null,
        'is_default' => true,
        'is_enabled' => true,
        'settings' => [
            'theme' => 'default',
            'logo' => null,
            'favicon' => null,
            'analytics' => [
                'google_analytics_id' => null,
                'facebook_pixel_id' => null,
            ],
            'seo' => [
                'meta_title' => 'Laravel Shopper Store',
                'meta_description' => 'Modern e-commerce store powered by Laravel Shopper',
                'meta_keywords' => 'ecommerce, laravel, vue, shopping',
            ],
            'social' => [
                'facebook' => null,
                'twitter' => null,
                'instagram' => null,
                'youtube' => null,
            ],
        ],
    ],
];
