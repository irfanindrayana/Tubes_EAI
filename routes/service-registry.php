<?php

use Illuminate\Support\Facades\Route;
use App\Services\ServiceRegistryClient;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Service Registry API Routes
|--------------------------------------------------------------------------
|
| API endpoints for service discovery and registry management
|
*/

Route::prefix('api/v1/services')->middleware(['api'])->group(function () {
    
    // Service registry endpoints
    Route::get('/registry', function () {
        $registryClient = new ServiceRegistryClient();
        return response()->json([
            'success' => true,
            'data' => $registryClient->getServiceStats()
        ]);
    });
    
    Route::get('/registry/{service}', function ($service) {
        $registryClient = new ServiceRegistryClient();
        $serviceInfo = $registryClient->getService($service);
        
        if (!$serviceInfo) {
            return response()->json([
                'success' => false,
                'message' => 'Service not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => $serviceInfo
        ]);
    });
    
    Route::get('/healthy', function () {
        $registryClient = new ServiceRegistryClient();
        return response()->json([
            'success' => true,
            'data' => $registryClient->getHealthyServices()
        ]);
    });
    
    Route::post('/registry', function (Request $request) {
        $request->validate([
            'name' => 'required|string',
            'host' => 'required|string',
            'port' => 'required|integer',
            'health_endpoint' => 'string',
            'version' => 'string',
            'environment' => 'string'
        ]);
        
        $registryClient = new ServiceRegistryClient();
        $success = $registryClient->registerService($request->all());
        
        return response()->json([
            'success' => $success,
            'message' => $success ? 'Service registered successfully' : 'Failed to register service'
        ], $success ? 201 : 500);
    });
    
    Route::delete('/registry/{service}', function ($service) {
        $registryClient = new ServiceRegistryClient();
        $success = $registryClient->unregisterService($service);
        
        return response()->json([
            'success' => $success,
            'message' => $success ? 'Service unregistered successfully' : 'Failed to unregister service'
        ], $success ? 200 : 500);
    });
    
    // Health check aggregation
    Route::get('/health', function () {
        $registryClient = new ServiceRegistryClient();
        $services = $registryClient->getServices();
        $healthStatus = [];
        
        foreach ($services as $serviceName => $service) {
            try {
                $healthUrl = "http://{$service['host']}:{$service['port']}{$service['health_endpoint']}";
                $response = Http::timeout(5)->get($healthUrl);
                
                $healthStatus[$serviceName] = [
                    'status' => $response->successful() ? 'healthy' : 'unhealthy',
                    'response_time' => $response->transferStats ? $response->transferStats->getTransferTime() : null,
                    'last_check' => now()->toISOString()
                ];
            } catch (\Exception $e) {
                $healthStatus[$serviceName] = [
                    'status' => 'unreachable',
                    'error' => $e->getMessage(),
                    'last_check' => now()->toISOString()
                ];
            }
        }
        
        $totalServices = count($services);
        $healthyServices = count(array_filter($healthStatus, fn($status) => $status['status'] === 'healthy'));
        
        return response()->json([
            'success' => true,
            'data' => [
                'overall_status' => $healthyServices === $totalServices ? 'healthy' : 'degraded',
                'total_services' => $totalServices,
                'healthy_services' => $healthyServices,
                'unhealthy_services' => $totalServices - $healthyServices,
                'services' => $healthStatus,
                'last_check' => now()->toISOString()
            ]
        ]);
    });
    
    // Service communication helper
    Route::post('/communicate/{service}', function (Request $request, $service) {
        $registryClient = new ServiceRegistryClient();
        $serviceUrl = $registryClient->getServiceUrl($service);
        
        if (!$serviceUrl) {
            return response()->json([
                'success' => false,
                'message' => 'Service not found or unavailable'
            ], 404);
        }
        
        $method = $request->input('method', 'GET');
        $path = $request->input('path', '/');
        $data = $request->input('data', []);
        $headers = $request->input('headers', []);
        
        try {
            $url = rtrim($serviceUrl, '/') . '/' . ltrim($path, '/');
            
            $httpClient = Http::withHeaders(array_merge([
                'X-Service-Source' => env('SERVICE_NAME', 'api-gateway'),
                'X-Request-ID' => uniqid('req_', true)
            ], $headers));
            
            $response = match(strtoupper($method)) {
                'GET' => $httpClient->get($url, $data),
                'POST' => $httpClient->post($url, $data),
                'PUT' => $httpClient->put($url, $data),
                'DELETE' => $httpClient->delete($url),
                default => throw new \InvalidArgumentException("Unsupported HTTP method: $method")
            };
            
            return response()->json([
                'success' => $response->successful(),
                'status_code' => $response->status(),
                'data' => $response->json(),
                'headers' => $response->headers()
            ], $response->status());
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Communication failed: ' . $e->getMessage()
            ], 500);
        }
    });
    
    // Load balancing endpoint
    Route::get('/load-balance/{service}', function ($service) {
        $registryClient = new ServiceRegistryClient();
        $serviceUrl = $registryClient->getServiceUrl($service, true);
        
        return response()->json([
            'success' => true,
            'data' => [
                'service' => $service,
                'url' => $serviceUrl,
                'load_balanced' => true
            ]
        ]);
    });
    
    // Service metrics aggregation
    Route::get('/metrics', function () {
        $registryClient = new ServiceRegistryClient();
        $services = $registryClient->getServices();
        $metrics = [];
        
        foreach ($services as $serviceName => $service) {
            try {
                $metricsUrl = "http://{$service['host']}:{$service['port']}/metrics";
                $response = Http::timeout(3)->get($metricsUrl);
                
                if ($response->successful()) {
                    $metrics[$serviceName] = $response->json();
                }
            } catch (\Exception $e) {
                // Metrics endpoint might not exist, skip silently
            }
        }
        
        return response()->json([
            'success' => true,
            'data' => $metrics,
            'collected_at' => now()->toISOString()
        ]);
    });
    
    // Service discovery configuration
    Route::get('/discovery/config', function () {
        return response()->json([
            'success' => true,
            'data' => [
                'service_name' => env('SERVICE_NAME', 'unknown-service'),
                'service_port' => env('SERVICE_PORT', '8000'),
                'microservice_mode' => env('MICROSERVICE_MODE', false),
                'enable_internal_api' => env('ENABLE_INTERNAL_API', false),
                'registry_ttl' => 60, // seconds
                'refresh_interval' => 30, // seconds
                'health_check_interval' => 30, // seconds
                'inter_service_communication' => [
                    'user_service_url' => env('USER_SERVICE_URL'),
                    'ticketing_service_url' => env('TICKETING_SERVICE_URL'),
                    'payment_service_url' => env('PAYMENT_SERVICE_URL'),
                    'inbox_service_url' => env('INBOX_SERVICE_URL'),
                    'reviews_service_url' => env('REVIEWS_SERVICE_URL')
                ]
            ]
        ]);
    });
});
