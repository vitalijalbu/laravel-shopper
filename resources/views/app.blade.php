<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} - Control Panel</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600&display=swap" rel="stylesheet" />

    <!-- Shopper Global Configuration -->
    <script>
        window.ShopperConfig = {
            locale: '{{ app()->getLocale() }}',
            translations: @json(file_exists(resource_path('lang/' . app()->getLocale() . '/shopper.php')) ? trans('shopper', [], app()->getLocale()) : []),
            csrf_token: '{{ csrf_token() }}',
            app_url: '{{ config('app.url') }}',
            timezone: '{{ config('app.timezone') }}',
            currency: '{{ config('shopper.currency', 'USD') }}',
        };
    </script>

    @if(\Shopper\Support\Asset::isBuilt())
        {!! \Shopper\Support\Asset::styles() !!}
        {!! \Shopper\Support\Asset::scripts() !!}
    @else
        @vite(['resources/js/app.js', 'resources/css/app.css'])
    @endif
    @inertiaHead
</head>
<body class="font-sans antialiased">
    @inertia
</body>
</html>
