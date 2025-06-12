<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class ServiceRoutingProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadServiceSpecificRoutes();
    }

    /**
     * Load routes based on service name
     */
    protected function loadServiceSpecificRoutes(): void
    {
        $serviceName = env('SERVICE_NAME', 'api-gateway');
        
        // Load service-specific routes if they exist
        $serviceRouteFile = base_path("routes/{$serviceName}.php");
        
        if (file_exists($serviceRouteFile)) {
            Route::middleware('web')
                ->group($serviceRouteFile);
        }
        
        // For microservice mode, also ensure internal API routes are loaded
        if (env('MICROSERVICE_MODE', false) && env('ENABLE_INTERNAL_API', false)) {
            $internalApiFile = base_path('routes/internal-api.php');
            if (file_exists($internalApiFile)) {
                Route::middleware('api')
                    ->group($internalApiFile);
            }
        }
    }
}
