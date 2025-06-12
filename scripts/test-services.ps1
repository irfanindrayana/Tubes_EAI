#!/usr/bin/env powershell

# Trans Bandung Microservices Testing Script
# Tests all service endpoints and inter-service communication

param(
    [string]$Service = "all",
    [switch]$Verbose = $false
)

Write-Host "🧪 Trans Bandung Microservices Testing" -ForegroundColor Green
Write-Host "======================================" -ForegroundColor Green

# Test configuration
$services = @{
    "user-service" = @{
        "url" = "http://localhost:8001"
        "health" = "/health"
        "endpoints" = @("/api/v1/internal/users/1", "/auth/user")
    }
    "ticketing-service" = @{
        "url" = "http://localhost:8002"
        "health" = "/health"
        "endpoints" = @("/api/v1/internal/ticketing/routes/1", "/ticketing/routes")
    }
    "payment-service" = @{
        "url" = "http://localhost:8003"
        "health" = "/health"
        "endpoints" = @("/api/v1/internal/payments/methods", "/payments/methods")
    }
    "inbox-service" = @{
        "url" = "http://localhost:8004"
        "health" = "/health"
        "endpoints" = @("/api/v1/internal/inbox/user/1/messages")
    }
    "api-gateway" = @{
        "url" = "http://localhost:8000"
        "health" = "/health"
        "endpoints" = @("/api/v1/internal/users/1", "/")
    }
    "load-balancer" = @{
        "url" = "http://localhost"
        "health" = "/health"
        "endpoints" = @("/status", "/")
    }
}

function Test-ServiceEndpoint {
    param(
        [string]$Name,
        [string]$Url,
        [int]$TimeoutSec = 10
    )
    
    try {
        $response = Invoke-RestMethod -Uri $Url -TimeoutSec $TimeoutSec -ErrorAction Stop
        if ($Verbose) {
            Write-Host "    Response: $($response | ConvertTo-Json -Compress)" -ForegroundColor Gray
        }
        return @{ Success = $true; Status = $response.status; Response = $response }
    }
    catch {
        return @{ Success = $false; Error = $_.Exception.Message; Response = $null }
    }
}

function Test-Service {
    param(
        [string]$ServiceName,
        [hashtable]$ServiceConfig
    )
    
    Write-Host "🔍 Testing $ServiceName..." -ForegroundColor Yellow
    
    # Test health endpoint
    $healthUrl = $ServiceConfig.url + $ServiceConfig.health
    $healthResult = Test-ServiceEndpoint -Name "Health Check" -Url $healthUrl
    
    if ($healthResult.Success) {
        Write-Host "  ✅ Health Check: $($healthResult.Status)" -ForegroundColor Green
    } else {
        Write-Host "  ❌ Health Check: $($healthResult.Error)" -ForegroundColor Red
        return $false
    }
    
    # Test other endpoints
    $allEndpointsPass = $true
    foreach ($endpoint in $ServiceConfig.endpoints) {
        $testUrl = $ServiceConfig.url + $endpoint
        $result = Test-ServiceEndpoint -Name $endpoint -Url $testUrl -TimeoutSec 5
        
        if ($result.Success) {
            Write-Host "  ✅ $endpoint" -ForegroundColor Green
        } else {
            Write-Host "  ⚠️  $endpoint : $($result.Error)" -ForegroundColor Yellow
            if ($result.Error -notlike "*404*" -and $result.Error -notlike "*401*") {
                $allEndpointsPass = $false
            }
        }
    }
    
    return $allEndpointsPass
}

function Test-InterServiceCommunication {
    Write-Host "🔗 Testing Inter-Service Communication..." -ForegroundColor Yellow
    
    # Test 1: User service to User service (self-test)
    try {
        $userResponse = Invoke-RestMethod -Uri "http://localhost:8001/api/v1/internal/users/1" -TimeoutSec 10
        Write-Host "  ✅ User Service Internal API: Working" -ForegroundColor Green
    }
    catch {
        Write-Host "  ❌ User Service Internal API: $($_.Exception.Message)" -ForegroundColor Red
    }
    
    # Test 2: API Gateway routing to services
    try {
        $gatewayToUser = Invoke-RestMethod -Uri "http://localhost:8000/api/v1/internal/users/1" -TimeoutSec 10
        Write-Host "  ✅ API Gateway → User Service: Working" -ForegroundColor Green
    }
    catch {
        Write-Host "  ❌ API Gateway → User Service: $($_.Exception.Message)" -ForegroundColor Red
    }
    
    # Test 3: Load balancer routing
    try {
        $lbHealth = Invoke-RestMethod -Uri "http://localhost/health" -TimeoutSec 10
        Write-Host "  ✅ Load Balancer → API Gateway: Working" -ForegroundColor Green
    }
    catch {
        Write-Host "  ❌ Load Balancer → API Gateway: $($_.Exception.Message)" -ForegroundColor Red
    }
}

function Test-DatabaseConnections {
    Write-Host "🗄️  Testing Database Connections..." -ForegroundColor Yellow
    
    $databases = @(
        "transbandung-user-db",
        "transbandung-ticketing-db", 
        "transbandung-payment-db",
        "transbandung-inbox-db",
        "transbandung-redis"
    )
    
    foreach ($db in $databases) {
        try {
            $status = docker exec $db sh -c "echo 'SELECT 1' | mysql -u microservice -pmicroservice123 2>/dev/null || redis-cli ping 2>/dev/null"
            if ($status -eq "1" -or $status -eq "PONG") {
                Write-Host "  ✅ $db: Connected" -ForegroundColor Green
            } else {
                Write-Host "  ❌ $db: Connection failed" -ForegroundColor Red
            }
        }
        catch {
            Write-Host "  ❌ $db: $($_.Exception.Message)" -ForegroundColor Red
        }
    }
}

# Main testing logic
try {
    if ($Service -eq "all") {
        # Test all services
        $allPassed = $true
        foreach ($serviceName in $services.Keys) {
            $result = Test-Service -ServiceName $serviceName -ServiceConfig $services[$serviceName]
            if (!$result) {
                $allPassed = $false
            }
            Write-Host ""
        }
        
        # Additional tests
        Test-InterServiceCommunication
        Write-Host ""
        Test-DatabaseConnections
        
        # Summary
        Write-Host "`n📊 Test Summary:" -ForegroundColor Green
        if ($allPassed) {
            Write-Host "✅ All critical tests passed!" -ForegroundColor Green
        } else {
            Write-Host "⚠️  Some tests failed - check logs above" -ForegroundColor Yellow
        }
        
    } else {
        # Test specific service
        if ($services.ContainsKey($Service)) {
            Test-Service -ServiceName $Service -ServiceConfig $services[$Service]
        } else {
            Write-Host "❌ Unknown service: $Service" -ForegroundColor Red
            Write-Host "Available services: $($services.Keys -join ', ')" -ForegroundColor Gray
        }
    }
    
    # Show container status
    Write-Host "`n🐳 Container Status:" -ForegroundColor Green
    docker-compose -f docker-compose.services.yml ps

}
catch {
    Write-Host "`n❌ Testing failed: $($_.Exception.Message)" -ForegroundColor Red
    exit 1
}
