<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Http\Middleware\ServiceDiscovery;

class ServiceRegistryClient
{
    private $services = [];
    private $lastRefresh = null;
    private $refreshInterval = 30; // seconds
    
    /**
     * Get all registered services
     */
    public function getServices($refresh = false)
    {
        if ($refresh || $this->shouldRefresh()) {
            $this->refreshServices();
        }
        
        return $this->services;
    }
    
    /**
     * Get specific service by name
     */
    public function getService($serviceName)
    {
        $services = $this->getServices();
        return $services[$serviceName] ?? null;
    }
    
    /**
     * Get healthy services only
     */
    public function getHealthyServices()
    {
        return array_filter($this->getServices(), function($service) {
            return $this->checkServiceHealth($service);
        });
    }
    
    /**
     * Get service URL with load balancing
     */
    public function getServiceUrl($serviceName, $loadBalance = true)
    {
        $service = $this->getService($serviceName);
        
        if (!$service) {
            // Fallback to container hostname
            return "http://{$serviceName}";
        }
        
        if ($loadBalance) {
            return $this->getLoadBalancedUrl($serviceName);
        }
        
        return "http://{$service['host']}:{$service['port']}";
    }
    
    /**
     * Register a service
     */
    public function registerService($serviceInfo)
    {
        try {
            $serviceName = $serviceInfo['name'];
            $key = "service:registry:{$serviceName}";
            
            Redis::setex($key, 60, json_encode($serviceInfo));
            
            Log::info("Service registered via client", ['service' => $serviceName]);
            
            return true;
            
        } catch (\Exception $e) {
            Log::error("Service registration failed", ['error' => $e->getMessage()]);
            return false;
        }
    }
    
    /**
     * Unregister a service
     */
    public function unregisterService($serviceName)
    {
        try {
            $key = "service:registry:{$serviceName}";
            Redis::del($key);
            
            Log::info("Service unregistered", ['service' => $serviceName]);
            
            return true;
            
        } catch (\Exception $e) {
            Log::error("Service unregistration failed", ['error' => $e->getMessage()]);
            return false;
        }
    }
    
    /**
     * Refresh services from registry
     */
    private function refreshServices()
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
            
            $this->services = $services;
            $this->lastRefresh = time();
            
            Log::debug("Services refreshed", ['count' => count($services)]);
            
        } catch (\Exception $e) {
            Log::error("Service refresh failed", ['error' => $e->getMessage()]);
        }
    }
    
    /**
     * Check if services should be refreshed
     */
    private function shouldRefresh()
    {
        return !$this->lastRefresh || 
               (time() - $this->lastRefresh) > $this->refreshInterval;
    }
    
    /**
     * Check service health
     */
    private function checkServiceHealth($service)
    {
        try {
            $url = "http://{$service['host']}:{$service['port']}{$service['health_endpoint']}";
            $response = Http::timeout(5)->get($url);
            
            return $response->successful() && 
                   isset($response->json()['status']) && 
                   $response->json()['status'] === 'healthy';
                   
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Get load balanced URL for service
     */
    private function getLoadBalancedUrl($serviceName)
    {
        // For now, return the primary service URL
        // In a full implementation, this would handle multiple instances
        $service = $this->getService($serviceName);
        
        if (!$service) {
            return "http://{$serviceName}";
        }
        
        return "http://{$service['host']}:{$service['port']}";
    }
    
    /**
     * Get service statistics
     */
    public function getServiceStats()
    {
        $services = $this->getServices(true);
        $healthy = $this->getHealthyServices();
        
        return [
            'total_services' => count($services),
            'healthy_services' => count($healthy),
            'unhealthy_services' => count($services) - count($healthy),
            'last_refresh' => $this->lastRefresh ? date('Y-m-d H:i:s', $this->lastRefresh) : null,
            'services' => $services
        ];
    }
}
