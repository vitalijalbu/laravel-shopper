<?php

namespace Shopper\CP;

class Navigation
{
    protected static $nav = [];

    protected static $preferences = [];

    public static function make()
    {
        return new static;
    }

    public static function build()
    {
        $nav = [
            'dashboard' => [
                'display' => 'Dashboard',
                'url' => '/cp',
                'icon' => 'dashboard',
                'children' => [],
            ],
            'collections' => [
                'display' => 'Collections',
                'url' => '/cp/collections',
                'icon' => 'collection',
                'children' => static::getCollectionNavItems(),
            ],
            'ecommerce' => [
                'display' => 'E-commerce',
                'url' => '/cp/ecommerce',
                'icon' => 'shopping-cart',
                'children' => [
                    'products' => [
                        'display' => 'Products',
                        'url' => '/cp/collections/products',
                        'icon' => 'box',
                    ],
                    'orders' => [
                        'display' => 'Orders',
                        'url' => '/cp/collections/orders',
                        'icon' => 'document-text',
                    ],
                    'customers' => [
                        'display' => 'Customers',
                        'url' => '/cp/collections/customers',
                        'icon' => 'users',
                    ],
                    'categories' => [
                        'display' => 'Categories',
                        'url' => '/cp/collections/categories',
                        'icon' => 'folder',
                    ],
                    'inventory' => [
                        'display' => 'Inventory',
                        'url' => '/cp/ecommerce/inventory',
                        'icon' => 'archive',
                    ],
                    'analytics' => [
                        'display' => 'Analytics',
                        'url' => '/cp/ecommerce/analytics',
                        'icon' => 'chart-bar',
                    ],
                ],
            ],
            'fields' => [
                'display' => 'Fields',
                'url' => '/cp/fields',
                'icon' => 'template',
                'children' => [
                    'blueprints' => [
                        'display' => 'Blueprints',
                        'url' => '/cp/fields/blueprints',
                        'icon' => 'document-duplicate',
                    ],
                    'fieldsets' => [
                        'display' => 'Fieldsets',
                        'url' => '/cp/fields/fieldsets',
                        'icon' => 'clipboard-list',
                    ],
                ],
            ],
            'sites' => [
                'display' => 'Sites',
                'url' => '/cp/sites',
                'icon' => 'globe',
                'children' => static::getSiteNavItems(),
            ],
            'users' => [
                'display' => 'Users',
                'url' => '/cp/users',
                'icon' => 'user-group',
                'children' => [
                    'users' => [
                        'display' => 'All Users',
                        'url' => '/cp/users',
                        'icon' => 'users',
                    ],
                    'roles' => [
                        'display' => 'Roles',
                        'url' => '/cp/users/roles',
                        'icon' => 'key',
                    ],
                    'permissions' => [
                        'display' => 'Permissions',
                        'url' => '/cp/users/permissions',
                        'icon' => 'shield-check',
                    ],
                ],
            ],
            'assets' => [
                'display' => 'Assets',
                'url' => '/cp/assets',
                'icon' => 'photograph',
                'children' => [],
            ],
            'utilities' => [
                'display' => 'Utilities',
                'url' => '/cp/utilities',
                'icon' => 'cog',
                'children' => [
                    'cache' => [
                        'display' => 'Cache Manager',
                        'url' => '/cp/utilities/cache',
                        'icon' => 'lightning-bolt',
                    ],
                    'updates' => [
                        'display' => 'Updates',
                        'url' => '/cp/utilities/updates',
                        'icon' => 'refresh',
                    ],
                    'phpinfo' => [
                        'display' => 'PHP Info',
                        'url' => '/cp/utilities/phpinfo',
                        'icon' => 'code',
                    ],
                ],
            ],
        ];

        // Merge custom nav items
        foreach (static::$nav as $section => $items) {
            if (isset($nav[$section])) {
                $nav[$section]['children'] = array_merge($nav[$section]['children'], $items);
            } else {
                $nav[$section] = $items;
            }
        }

        return static::filterByPermissions($nav);
    }

    public static function extend($section, $items)
    {
        if (! isset(static::$nav[$section])) {
            static::$nav[$section] = [];
        }

        static::$nav[$section] = array_merge(static::$nav[$section], $items);
    }

    protected static function getCollectionNavItems()
    {
        $collections = static::getCollections();
        $items = [];

        foreach ($collections as $collection) {
            $items[$collection['handle']] = [
                'display' => $collection['title'],
                'url' => "/cp/collections/{$collection['handle']}",
                'icon' => $collection['icon'] ?? 'collection',
                'collection' => $collection['handle'],
            ];
        }

        return $items;
    }

    protected static function getSiteNavItems()
    {
        $sites = static::getSites();
        $items = [];

        foreach ($sites as $site) {
            $items[$site['handle']] = [
                'display' => $site['name'],
                'url' => "/cp/sites/{$site['handle']}",
                'icon' => 'globe',
                'site' => $site['handle'],
            ];
        }

        return $items;
    }

    protected static function getCollections()
    {
        // This would fetch from your collection storage
        return [
            ['handle' => 'products', 'title' => 'Products', 'icon' => 'box'],
            ['handle' => 'orders', 'title' => 'Orders', 'icon' => 'document-text'],
            ['handle' => 'customers', 'title' => 'Customers', 'icon' => 'users'],
            ['handle' => 'categories', 'title' => 'Categories', 'icon' => 'folder'],
            ['handle' => 'pages', 'title' => 'Pages', 'icon' => 'document'],
            ['handle' => 'blog', 'title' => 'Blog', 'icon' => 'pencil'],
        ];
    }

    protected static function getSites()
    {
        // This would fetch from your site configuration
        return [
            ['handle' => 'default', 'name' => 'Main Site', 'url' => 'https://example.com'],
            ['handle' => 'blog', 'name' => 'Blog', 'url' => 'https://blog.example.com'],
            ['handle' => 'shop', 'name' => 'Shop', 'url' => 'https://shop.example.com'],
        ];
    }

    protected static function filterByPermissions($nav)
    {
        // Filter navigation items based on user permissions
        $user = auth()->user();

        if (! $user) {
            return [];
        }

        $filtered = [];

        foreach ($nav as $key => $item) {
            if (static::userCanAccess($user, $key, $item)) {
                if (isset($item['children']) && ! empty($item['children'])) {
                    $item['children'] = static::filterByPermissions($item['children']);
                }
                $filtered[$key] = $item;
            }
        }

        return $filtered;
    }

    protected static function userCanAccess($user, $key, $item)
    {
        try {
            // Check if user has permission to access this nav item
            $permission = "access_{$key}";

            // Super admin can access everything
            if (method_exists($user, 'can') && $user->can('super_admin')) {
                return true;
            }

            // Check specific permissions
            if (method_exists($user, 'can')) {
                return $user->can($permission) || $user->can('access_cp');
            }

            // Default: allow access if permission system is not available
            return true;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Navigation permission check failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id ?? null,
                'key' => $key,
            ]);

            // Default to true to avoid blocking access
            return true;
        }
    }

    public static function breadcrumbs($currentUrl)
    {
        $crumbs = [];
        $nav = static::build();

        // Home breadcrumb
        $crumbs[] = [
            'title' => 'Control Panel',
            'url' => '/cp',
        ];

        // Find current page in navigation
        $parts = explode('/', trim($currentUrl, '/'));

        if (count($parts) >= 2 && $parts[0] === 'cp') {
            $section = $parts[1] ?? null;

            if ($section && isset($nav[$section])) {
                $crumbs[] = [
                    'title' => $nav[$section]['display'],
                    'url' => $nav[$section]['url'],
                ];

                // Add subsection if exists
                if (count($parts) >= 3) {
                    $subsection = $parts[2];
                    if (isset($nav[$section]['children'][$subsection])) {
                        $crumbs[] = [
                            'title' => $nav[$section]['children'][$subsection]['display'],
                            'url' => $nav[$section]['children'][$subsection]['url'],
                        ];
                    }
                }
            }
        }

        return $crumbs;
    }

    public static function active($url, $currentUrl)
    {
        return str_starts_with($currentUrl, $url);
    }

    public static function setPreferences($preferences)
    {
        static::$preferences = $preferences;
    }

    public static function getPreferences()
    {
        return static::$preferences;
    }

    public static function toArray()
    {
        return [
            'nav' => static::getSimpleNavigation(),
            'preferences' => static::getPreferences(),
            'breadcrumbs' => [],
            'user' => \Illuminate\Support\Facades\Auth::user(),
            'sites' => static::getSites(),
        ];
    }

    public static function tree(): array
    {
        try {
            return [
                'sections' => [],
                'items' => static::getSimpleNavigation(),
            ];
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Navigation tree error', [
                'error' => $e->getMessage(),
            ]);

            return [
                'sections' => [],
                'items' => static::getSimpleNavigation(),
            ];
        }
    }

    /**
     * Get simple navigation without permission checks to avoid timeouts
     */
    public static function getSimpleNavigation(): array
    {
        return [
            'dashboard' => [
                'display' => 'Dashboard',
                'url' => '/cp',
                'icon' => 'home',
                'children' => [],
            ],
            'collections' => [
                'display' => 'Collections',
                'url' => '/cp/collections',
                'icon' => 'folder',
                'children' => [],
            ],
            'apps' => [
                'display' => 'Apps',
                'url' => '/cp/apps',
                'icon' => 'grid',
                'children' => [],
            ],
        ];
    }
}
