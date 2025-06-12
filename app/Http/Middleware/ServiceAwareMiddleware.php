<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ServiceAwareMiddleware
{
    /**
     * Handle an incoming request and set service-specific context
     */
    public function handle(Request $request, Closure $next)
    {
        // Get service name from environment
        $serviceName = env('SERVICE_NAME', 'api-gateway');
        
        // Set service context in request
        $request->attributes->set('service_name', $serviceName);
        $request->attributes->set('service_port', env('SERVICE_PORT', 8000));
        
        // Add service headers
        $response = $next($request);
        
        if (method_exists($response, 'header')) {
            $response->header('X-Service-Name', $serviceName);
            $response->header('X-Service-Port', env('SERVICE_PORT', 8000));
            $response->header('X-Service-Environment', env('SERVICE_ENVIRONMENT', 'containerized'));
        }
        
        return $response;
    }
}
