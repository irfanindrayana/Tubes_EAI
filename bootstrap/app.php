<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'service.discovery' => \App\Http\Middleware\ServiceDiscovery::class,
            'service.aware' => \App\Http\Middleware\ServiceAwareMiddleware::class,
        ]);
        
        // Register service discovery middleware globally for microservices
        if (env('MICROSERVICE_MODE', false)) {
            $middleware->web(append: [
                \App\Http\Middleware\ServiceDiscovery::class,
                \App\Http\Middleware\ServiceAwareMiddleware::class,
            ]);
            
            $middleware->api(append: [
                \App\Http\Middleware\ServiceDiscovery::class,
                \App\Http\Middleware\ServiceAwareMiddleware::class,
            ]);
        }
        
        // Configure CSRF middleware to exclude GraphQL endpoints
        $middleware->validateCsrfTokens(except: [
            'graphql',
            'graphql/*',
            '/graphql',
            '/graphql/*',
            'api/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
