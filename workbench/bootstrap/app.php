<?php

use Illuminate\Foundation\Application;
use VitaliJalbu\LaravelShopper\ShopperServiceProvider;

return Application::configure(basePath: dirname(__DIR__))
    ->withProviders([
        ShopperServiceProvider::class,
    ])
    ->withRouting(
        web: __DIR__.'/../../routes/cp.php',
        api: __DIR__.'/../../routes/api.php',
        commands: __DIR__.'/routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (\Illuminate\Foundation\Configuration\Middleware $middleware) {
        $middleware->web(append: [
            \Illuminate\Http\Middleware\HandleCors::class,
            \VitaliJalbu\LaravelShopper\Http\Middleware\ShopperMiddleware::class,
        ]);
    })
    ->withExceptions(function (\Illuminate\Foundation\Configuration\Exceptions $exceptions) {
        //
    })
    ->create();
