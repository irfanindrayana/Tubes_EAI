#!/usr/bin/env powershell

# Individual Service Scaling Script
# Scale specific microservices based on load requirements

param(
    [Parameter(Mandatory=$true)]
    [string]$ServiceName,
    [Parameter(Mandatory=$true)]
    [int]$Replicas,
    [switch]$Force = $false,
    [switch]$AutoScale = $false,
    [int]$MaxReplicas = 10,
    [int]$MinReplicas = 1,
    [int]$CpuThreshold = 70
)

Write-Host "📈 Trans Bandung Service Scaling Tool" -ForegroundColor Green
Write-Host "====================================" -ForegroundColor Green

# Scalable services configuration
$scalableServices = @(
    "user-service",
    "ticketing-service", 
    "payment-service",
    "inbox-service",
    "reviews-service",
    "api-gateway"
)

function Write-Log {
    param($Message, $Level = "INFO")
    $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    $color = switch ($Level) {
        "SUCCESS" { "Green" }
        "WARN" { "Yellow" }
        "ERROR" { "Red" }
        default { "White" }
    }
    Write-Host "[$timestamp] [$Level] $Message" -ForegroundColor $color
}

try {
    # Validate service name
    if (!$scalableServices.ContainsKey($Service)) {
        throw "Service '$Service' is not scalable. Available services: $($scalableServices.Keys -join ', ')"
    }

    $serviceConfig = $scalableServices[$Service]
    
    # Validate replica count
    if ($Replicas -lt $serviceConfig.min_replicas -or $Replicas -gt $serviceConfig.max_replicas) {
        if (!$Force) {
            throw "Replica count must be between $($serviceConfig.min_replicas) and $($serviceConfig.max_replicas). Use -Force to override."
        }
        Write-Host "⚠️  Warning: Replica count outside recommended range" -ForegroundColor Yellow
    }

    # Get current scale
    Write-Host "📊 Current service status..." -ForegroundColor Yellow
    $currentContainers = docker-compose -f $composeFile ps $Service --format "table {{.Name}}\t{{.State}}" | Select-Object -Skip 1
    $currentCount = ($currentContainers | Measure-Object).Count
    
    Write-Host "  Current replicas: $currentCount" -ForegroundColor Gray
    Write-Host "  Target replicas: $Replicas" -ForegroundColor Gray

    if ($currentCount -eq $Replicas) {
        Write-Host "✅ Service is already at the target scale" -ForegroundColor Green
        return
    }

    # Perform scaling
    Write-Host "⚖️  Scaling $Service to $Replicas replicas..." -ForegroundColor Yellow
    
    if ($Replicas -gt $currentCount) {
        Write-Host "  Scaling UP from $currentCount to $Replicas" -ForegroundColor Green
    } else {
        Write-Host "  Scaling DOWN from $currentCount to $Replicas" -ForegroundColor Red
    }

    # Scale the service
    docker-compose -f $composeFile up -d --scale $Service=$Replicas $Service
    
    if ($LASTEXITCODE -ne 0) {
        throw "Failed to scale service"
    }

    # Wait for containers to be ready
    Write-Host "⏳ Waiting for containers to be ready..." -ForegroundColor Yellow
    Start-Sleep 15

    # Update load balancer configuration if scaling API Gateway
    if ($Service -eq "api-gateway" -and $Replicas -gt 1) {
        Write-Host "🔄 Updating load balancer configuration..." -ForegroundColor Yellow
        # In a real setup, you would update Nginx upstream configuration here
        # For now, we'll just restart the load balancer
        docker-compose -f $composeFile restart nginx-lb
    }

    # Health check scaled instances
    Write-Host "🔍 Performing health checks..." -ForegroundColor Yellow
    $healthyCount = 0
    $maxAttempts = 12  # 1 minute with 5-second intervals
    $attempt = 0

    do {
        Start-Sleep 5
        $attempt++
        $healthyCount = 0
        
        for ($i = 1; $i -le $Replicas; $i++) {
            $port = $serviceConfig.ports[$i-1]
            if ($port) {
                try {
                    $health = Invoke-RestMethod -Uri "http://localhost:$port/health" -TimeoutSec 5
                    if ($health.status -eq "healthy") {
                        $healthyCount++
                    }
                }
                catch {
                    # Service not ready yet
                }
            }
        }
        
        Write-Host "    Healthy instances: $healthyCount/$Replicas (attempt $attempt/$maxAttempts)" -ForegroundColor Gray
        
    } while ($healthyCount -lt $Replicas -and $attempt -lt $maxAttempts)

    # Final status
    Write-Host "`n📊 Scaling Results:" -ForegroundColor Green
    docker-compose -f $composeFile ps $Service

    if ($healthyCount -eq $Replicas) {
        Write-Host "`n✅ Scaling completed successfully!" -ForegroundColor Green
        Write-Host "  All $Replicas instances are healthy" -ForegroundColor Green
    } else {
        Write-Host "`n⚠️  Scaling completed with warnings" -ForegroundColor Yellow
        Write-Host "  Only $healthyCount/$Replicas instances are healthy" -ForegroundColor Yellow
        Write-Host "  Check logs with: docker-compose -f $composeFile logs $Service" -ForegroundColor Gray
    }

    # Show service URLs
    Write-Host "`n🌐 Service Endpoints:" -ForegroundColor Green
    for ($i = 1; $i -le $Replicas; $i++) {
        $port = $serviceConfig.ports[$i-1]
        if ($port) {
            Write-Host "  Instance $i : http://localhost:$port" -ForegroundColor Cyan
        }
    }

    # Load balancing info
    if ($Replicas -gt 1) {
        Write-Host "`n⚖️  Load Balancing:" -ForegroundColor Green
        Write-Host "  Traffic will be distributed across all healthy instances" -ForegroundColor Gray
        Write-Host "  Main endpoint remains: http://localhost" -ForegroundColor Cyan
    }

}
catch {
    Write-Host "`n❌ Scaling failed: $($_.Exception.Message)" -ForegroundColor Red
    Write-Host "Current containers:" -ForegroundColor Gray
    docker-compose -f $composeFile ps $Service
    exit 1
}
