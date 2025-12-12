<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Route Configuration
    |--------------------------------------------------------------------------
    */

    'route' => [
        'uri' => '/graphql',
        'name' => 'graphql',
        'middleware' => [
            'web',
            \Nuwave\Lighthouse\Support\Http\Middleware\AcceptJson::class,
        ],
        'prefix' => '',
        'domain' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Schema Declaration
    |--------------------------------------------------------------------------
    */

    'schema' => [
        'register' => base_path('graphql/schema.graphql'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Namespaces
    |--------------------------------------------------------------------------
    */

    'namespaces' => [
        'models' => ['Cartino\\Models'],
        'queries' => 'Cartino\\GraphQL\\Queries',
        'mutations' => 'Cartino\\GraphQL\\Mutations',
        'subscriptions' => 'Cartino\\GraphQL\\Subscriptions',
        'interfaces' => 'Cartino\\GraphQL\\Interfaces',
        'unions' => 'Cartino\\GraphQL\\Unions',
        'scalars' => 'Cartino\\GraphQL\\Scalars',
        'directives' => ['Cartino\\GraphQL\\Directives'],
        'validators' => ['Cartino\\GraphQL\\Validators'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Security
    |--------------------------------------------------------------------------
    */

    'security' => [
        'max_query_complexity' => \GraphQL\Validator\Rules\QueryComplexity::DISABLED,
        'max_query_depth' => \GraphQL\Validator\Rules\QueryDepth::DISABLED,
        'disable_introspection' => \GraphQL\Validator\Rules\DisableIntrospection::DISABLED,
    ],

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */

    'pagination' => [
        'max_count' => 100,
    ],

    /*
    |--------------------------------------------------------------------------
    | Debug
    |--------------------------------------------------------------------------
    */

    'debug' => env('LIGHTHOUSE_DEBUG', env('APP_DEBUG', false)),

    /*
    |--------------------------------------------------------------------------
    | Error Handlers
    |--------------------------------------------------------------------------
    */

    'error_handlers' => [
        \Nuwave\Lighthouse\Execution\ExtensionErrorHandler::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Field Middleware
    |--------------------------------------------------------------------------
    */

    'field_middleware' => [
        \Nuwave\Lighthouse\Schema\Middleware\TrimStrings::class,
        \Nuwave\Lighthouse\Schema\Middleware\ConvertEmptyStringsToNull::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Global ID
    |--------------------------------------------------------------------------
    */

    'global_id_field' => '_id',

    /*
    |--------------------------------------------------------------------------
    | Batched Queries
    |--------------------------------------------------------------------------
    */

    'batched_queries' => true,

    /*
    |--------------------------------------------------------------------------
    | Transactional Mutations
    |--------------------------------------------------------------------------
    */

    'transactional_mutations' => true,

    /*
    |--------------------------------------------------------------------------
    | GraphQL Playground
    |--------------------------------------------------------------------------
    */

    'graphql_playground' => [
        'enabled' => env('LIGHTHOUSE_PLAYGROUND_ENABLED', true),
        'route' => '/graphql-playground',
        'middleware' => ['web'],
    ],
];
