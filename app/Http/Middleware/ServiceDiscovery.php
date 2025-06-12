<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

class ServiceDiscovery
{
    /**
     * Handle an incoming request and register/discover services
     */
    public function handle(Request $request, Closure $next)
    {
        $this->registerService();
        $this->discoverServices();
        
        return $next($request);
    }
    
    /**
     * Register this service in the service registry
     */
    private function registerService()
    {
        try {
            $serviceName = env('SERVICE_NAME', 'unknown-service');
            $servicePort = env('SERVICE_PORT', '8000');
            $serviceHost = gethostname();
            
            $serviceInfo = [
                'name' => $serviceName,
                'host' => $serviceHost,
                'port' => $servicePort,
                'health_endpoint' => '/health',
                'last_seen' => now()->toISOString(),
                'status' => 'healthy',
                'version' => config('app.version', '1.0.0'),
                'environment' => config('app.env', 'production')
            ];
            
            // Register in Redis with TTL
            Redis::setex("service:registry:{$serviceName}", 60, json_encode($serviceInfo));
            
            Log::info("Service registered", ['service' => $serviceName, 'info' => $serviceInfo]);
            
        } catch (\Exception $e) {
            Log::error("Service registration failed", ['error' => $e->getMessage()]);
        }
    }
    
    /**
     * Discover available services
     */
    private function discoverServices()
    {
        try {
            $pattern = "service:registry:*";
            $keys = Redis::keys($pattern);
            $services = [];
            
            foreach ($keys as $key) {
                $serviceData = Redis::get($key);
                if ($serviceData) {
                    $service = json_decode($serviceData, true);
                    $serviceName = str_replace('service:registry:', '', $key);
                    $services[$serviceName] = $service;
                }
            }
            
            // Cache discovered services for this request
            app()->instance('discovered.services', $services);
            
        } catch (\Exception $e) {
            Log::error("Service discovery failed", ['error' => $e->getMessage()]);
        }
    }
    
    /**
     * Get discovered services
     */
    public static function getServices()
    {
        return app('discovered.services', []);
    }
    
    /**
     * Get specific service information
     */
    public static function getService($serviceName)
    {
        $services = self::getServices();
        return $services[$serviceName] ?? null;
    }
    
    /**
     * Get service URL
     */
    public static function getServiceUrl($serviceName)
    {
        $service = self::getService($serviceName);
        if (!$service) {
            // Fallback to environment variables
            $envKey = strtoupper(str_replace('-', '_', $serviceName)) . '_SERVICE_URL';
            return env($envKey, "http://{$serviceName}");
        }
        
        return "http://{$service['host']}:{$service['port']}";
    }
}
