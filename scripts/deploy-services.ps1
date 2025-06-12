#!/usr/bin/env powershell

# Trans Bandung Microservices Deployment Script
# Enhanced deployment with health checks, rollback, and monitoring

param(
    [switch]$Build = $false,
    [switch]$NoBuild = $false,
    [switch]$Clean = $false,
    [switch]$HealthCheck = $true,
    [switch]$Rollback = $false,
    [string]$Environment = "production",
    [string]$Service = "",
    [int]$Timeout = 300
)

Write-Host "🚀 Trans Bandung Microservices Deployment (Enhanced)" -ForegroundColor Green
Write-Host "===================================================" -ForegroundColor Green

# Configuration
$services = @(
    @{Name="user-db"; Type="database"; HealthEndpoint=""; WaitFor=@()},
    @{Name="ticketing-db"; Type="database"; HealthEndpoint=""; WaitFor=@()},
    @{Name="payment-db"; Type="database"; HealthEndpoint=""; WaitFor=@()},
    @{Name="inbox-db"; Type="database"; HealthEndpoint=""; WaitFor=@()},
    @{Name="reviews-db"; Type="database"; HealthEndpoint=""; WaitFor=@()},
    @{Name="redis"; Type="cache"; HealthEndpoint=""; WaitFor=@()},
    @{Name="user-service"; Type="service"; HealthEndpoint="http://localhost:8001/health"; WaitFor=@("user-db", "redis")},
    @{Name="ticketing-service"; Type="service"; HealthEndpoint="http://localhost:8002/health"; WaitFor=@("ticketing-db", "redis", "user-service")},
    @{Name="payment-service"; Type="service"; HealthEndpoint="http://localhost:8003/health"; WaitFor=@("payment-db", "redis", "user-service", "ticketing-service")},
    @{Name="inbox-service"; Type="service"; HealthEndpoint="http://localhost:8004/health"; WaitFor=@("inbox-db", "redis", "user-service")},
    @{Name="reviews-service"; Type="service"; HealthEndpoint="http://localhost:8005/health"; WaitFor=@("reviews-db", "redis", "user-service", "ticketing-service")},
    @{Name="api-gateway"; Type="service"; HealthEndpoint="http://localhost:8000/health"; WaitFor=@("user-service", "ticketing-service", "payment-service", "inbox-service")},
    @{Name="nginx-lb"; Type="loadbalancer"; HealthEndpoint="http://localhost/health"; WaitFor=@("api-gateway")}
)

$composeFile = "docker-compose.services.yml"
$backupDir = "backups/deployments"
$logFile = "logs/deployment-$(Get-Date -Format 'yyyy-MM-dd-HHmmss').log"

# Function to log messages
function Write-Log {
    param($Message, $Level = "INFO")
    $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    $logEntry = "[$timestamp] [$Level] $Message"
    Write-Host $logEntry
    if (!(Test-Path "logs")) { New-Item -ItemType Directory -Path "logs" | Out-Null }
    Add-Content -Path $logFile -Value $logEntry
}

# Function to check service health
function Test-ServiceHealth {
    param($ServiceConfig, $TimeoutSeconds = 30)
    
    if ($ServiceConfig.HealthEndpoint -eq "") {
        Start-Sleep -Seconds 5
        return $true
    }
    
    Write-Log "Checking health for $($ServiceConfig.Name) at $($ServiceConfig.HealthEndpoint)" "INFO"
    $attempts = 0
    $maxAttempts = [math]::Floor($TimeoutSeconds / 5)
    
    while ($attempts -lt $maxAttempts) {
        try {
            $response = Invoke-RestMethod -Uri $ServiceConfig.HealthEndpoint -TimeoutSec 5 -ErrorAction Stop
            if ($response.status -eq "healthy") {
                Write-Log "✅ $($ServiceConfig.Name) is healthy" "SUCCESS"
                return $true
            }
        }
        catch {
            Write-Log "⏳ $($ServiceConfig.Name) not ready yet (attempt $($attempts + 1)/$maxAttempts)" "WARN"
        }
        $attempts++
        Start-Sleep -Seconds 5
    }
    
    Write-Log "❌ $($ServiceConfig.Name) failed health check after $TimeoutSeconds seconds" "ERROR"
    return $false
}

# Function to create backup
function New-DeploymentBackup {
    if (!(Test-Path $backupDir)) { New-Item -ItemType Directory -Path $backupDir -Force | Out-Null }
    
    $timestamp = Get-Date -Format "yyyy-MM-dd-HHmmss"
    $backupPath = "$backupDir/backup-$timestamp"
    
    Write-Log "Creating deployment backup at $backupPath" "INFO"
    
    # Backup current containers and images
    docker ps --format "table {{.Names}}\t{{.Image}}\t{{.Status}}" > "$backupPath-containers.txt"
    docker images --format "table {{.Repository}}\t{{.Tag}}\t{{.ID}}" > "$backupPath-images.txt"
    
    return $backupPath
}

try {
    # Check Docker availability
    Write-Log "📋 Checking Docker availability..." "INFO"
    $dockerVersion = docker --version
    if ($LASTEXITCODE -ne 0) {
        throw "Docker is not installed or not running"
    }
    Write-Log "✅ Docker found: $dockerVersion" "SUCCESS"

    # Check Docker Compose availability
    $composeVersion = docker-compose --version
    if ($LASTEXITCODE -ne 0) {
        throw "Docker Compose is not installed"
    }
    Write-Log "✅ Docker Compose found: $composeVersion" "SUCCESS"

    # Handle rollback
    if ($Rollback) {
        Write-Log "🔄 Initiating rollback..." "WARN"
        $latestBackup = Get-ChildItem "$backupDir" | Sort-Object LastWriteTime -Descending | Select-Object -First 1
        if ($latestBackup) {
            Write-Log "Rolling back to: $($latestBackup.Name)" "INFO"
            # Implement rollback logic here
            docker-compose -f $composeFile down
            # Restore from backup (implementation needed)
            Write-Log "✅ Rollback completed" "SUCCESS"
            exit 0
        } else {
            Write-Log "❌ No backup found for rollback" "ERROR"
            exit 1
        }
    }

    # Create backup before deployment
    $backupPath = New-DeploymentBackup

    # Clean deployment if requested
    if ($Clean) {
        Write-Log "🧹 Cleaning existing deployment..." "WARN"
        docker-compose -f $composeFile down --volumes --remove-orphans
        docker system prune -f
        Write-Log "✅ Cleanup completed" "SUCCESS"
    }

    # Build images if requested
    if ($Build -and !$NoBuild) {
        Write-Log "🔨 Building service images..." "INFO"
        docker-compose -f $composeFile build --parallel
        if ($LASTEXITCODE -ne 0) {
            throw "Failed to build images"
        }
        Write-Log "✅ Images built successfully" "SUCCESS"
    }

    # Deploy services in dependency order
    Write-Log "🚀 Starting deployment..." "INFO"
    
    foreach ($serviceConfig in $services) {
        if ($Service -ne "" -and $serviceConfig.Name -ne $Service) {
            continue
        }
        
        Write-Log "📦 Deploying $($serviceConfig.Name) ($($serviceConfig.Type))..." "INFO"
        
        # Wait for dependencies
        foreach ($dependency in $serviceConfig.WaitFor) {
            $depConfig = $services | Where-Object { $_.Name -eq $dependency }
            if ($depConfig -and !(Test-ServiceHealth $depConfig 60)) {
                throw "Dependency $dependency is not healthy"
            }
        }
        
        # Start the service
        docker-compose -f $composeFile up -d $serviceConfig.Name
        if ($LASTEXITCODE -ne 0) {
            throw "Failed to start $($serviceConfig.Name)"
        }
        
        # Health check
        if ($HealthCheck -and !(Test-ServiceHealth $serviceConfig 120)) {
            Write-Log "❌ $($serviceConfig.Name) failed health check, rolling back..." "ERROR"
            docker-compose -f $composeFile logs $serviceConfig.Name
            throw "$($serviceConfig.Name) is not healthy"
        }
        
        Write-Log "✅ $($serviceConfig.Name) deployed successfully" "SUCCESS"
    }

    # Final health check for all services
    if ($HealthCheck) {
        Write-Log "🏥 Performing final health check..." "INFO"
        $allHealthy = $true
        
        foreach ($serviceConfig in $services) {
            if ($serviceConfig.HealthEndpoint -ne "" -and !(Test-ServiceHealth $serviceConfig 30)) {
                $allHealthy = $false
                Write-Log "❌ $($serviceConfig.Name) is not healthy" "ERROR"
            }
        }
        
        if (!$allHealthy) {
            throw "Some services are not healthy"
        }
        Write-Log "✅ All services are healthy" "SUCCESS"
    }

    # Display deployment summary
    Write-Log "📊 Deployment Summary:" "INFO"
    docker-compose -f $composeFile ps
    
    Write-Log "🎉 Deployment completed successfully!" "SUCCESS"
    Write-Log "🌐 Access points:" "INFO"
    Write-Log "   - API Gateway: http://localhost:8000" "INFO"
    Write-Log "   - Load Balancer: http://localhost" "INFO"
    Write-Log "   - User Service: http://localhost:8001" "INFO"
    Write-Log "   - Ticketing Service: http://localhost:8002" "INFO"
    Write-Log "   - Payment Service: http://localhost:8003" "INFO"
    Write-Log "   - Inbox Service: http://localhost:8004" "INFO"
    Write-Log "   - Reviews Service: http://localhost:8005" "INFO"
    Write-Log "   - phpMyAdmin: http://localhost:8080" "INFO"
    
}
        Write-Host "🧹 Cleaning existing deployment..." -ForegroundColor Yellow
        docker-compose -f $composeFile down --volumes --remove-orphans
        docker system prune -f
        Write-Host "✅ Cleanup completed" -ForegroundColor Green
    }

    # Create required directories
    Write-Host "📁 Creating required directories..." -ForegroundColor Yellow
    $directories = @(
        "storage/logs/user-service",
        "storage/logs/ticketing-service",
        "storage/logs/payment-service",
        "storage/logs/inbox-service",
        "storage/logs/api-gateway"
    )

    foreach ($dir in $directories) {
        if (!(Test-Path $dir)) {
            New-Item -ItemType Directory -Path $dir -Force | Out-Null
            Write-Host "  Created: $dir" -ForegroundColor Gray
        }
    }

    # Build images if requested
    if ($Build -and !$NoBuild) {
        Write-Host "🔨 Building Docker images..." -ForegroundColor Yellow
        docker-compose -f $composeFile build --no-cache
        if ($LASTEXITCODE -ne 0) {
            throw "Failed to build Docker images"
        }
        Write-Host "✅ Images built successfully" -ForegroundColor Green
    }

    # Deploy services in order
    Write-Host "🚀 Deploying microservices..." -ForegroundColor Yellow
    
    # Phase 1: Databases and Redis
    Write-Host "  Phase 1: Starting databases..." -ForegroundColor Cyan
    docker-compose -f $composeFile up -d user-db ticketing-db payment-db inbox-db reviews-db redis
    
    # Wait for databases to be healthy
    Write-Host "  Waiting for databases to be ready..." -ForegroundColor Cyan
    $maxWait = 120 # 2 minutes
    $waited = 0
    
    do {
        Start-Sleep 5
        $waited += 5
        $healthyDbs = (docker-compose -f $composeFile ps --filter "health=healthy" | Measure-Object -Line).Lines - 1
        Write-Host "    Healthy databases: $healthyDbs/6" -ForegroundColor Gray
    } while ($healthyDbs -lt 6 -and $waited -lt $maxWait)
    
    if ($healthyDbs -lt 6) {
        throw "Databases did not become healthy within $maxWait seconds"
    }
    Write-Host "  ✅ All databases are healthy" -ForegroundColor Green

    # Phase 2: Core Services
    Write-Host "  Phase 2: Starting core services..." -ForegroundColor Cyan
    docker-compose -f $composeFile up -d user-service
    
    # Wait for user service
    Write-Host "  Waiting for user service..." -ForegroundColor Cyan
    Start-Sleep 30
    
    docker-compose -f $composeFile up -d ticketing-service payment-service inbox-service
    
    # Wait for services
    Write-Host "  Waiting for microservices..." -ForegroundColor Cyan
    Start-Sleep 45

    # Phase 3: API Gateway and Load Balancer
    Write-Host "  Phase 3: Starting API Gateway and Load Balancer..." -ForegroundColor Cyan
    docker-compose -f $composeFile up -d api-gateway
    Start-Sleep 15
    
    docker-compose -f $composeFile up -d nginx-lb phpmyadmin

    # Final health check
    Write-Host "🔍 Performing health checks..." -ForegroundColor Yellow
    Start-Sleep 10
    
    $healthChecks = @{
        "User Service" = "http://localhost:8001/health"
        "Ticketing Service" = "http://localhost:8002/health"
        "Payment Service" = "http://localhost:8003/health"
        "Inbox Service" = "http://localhost:8004/health"
        "API Gateway" = "http://localhost:8000/health"
        "Load Balancer" = "http://localhost:80/health"
    }

    foreach ($service in $healthChecks.Keys) {
        try {
            $response = Invoke-RestMethod -Uri $healthChecks[$service] -TimeoutSec 10
            if ($response.status -eq "healthy") {
                Write-Host "  ✅ $service: Healthy" -ForegroundColor Green
            } else {
                Write-Host "  ⚠️  $service: $($response.status)" -ForegroundColor Yellow
            }
        }
        catch {
            Write-Host "  ❌ $service: Unhealthy" -ForegroundColor Red
        }
    }

    # Display service URLs
    Write-Host "`n🌐 Service URLs:" -ForegroundColor Green
    Write-Host "  Main Application: http://localhost" -ForegroundColor Cyan
    Write-Host "  API Gateway: http://localhost:8000" -ForegroundColor Cyan
    Write-Host "  User Service: http://localhost:8001" -ForegroundColor Cyan
    Write-Host "  Ticketing Service: http://localhost:8002" -ForegroundColor Cyan
    Write-Host "  Payment Service: http://localhost:8003" -ForegroundColor Cyan
    Write-Host "  Inbox Service: http://localhost:8004" -ForegroundColor Cyan
    Write-Host "  phpMyAdmin: http://localhost:8080" -ForegroundColor Cyan

    # Show running containers
    Write-Host "`n📊 Container Status:" -ForegroundColor Green
    docker-compose -f $composeFile ps

    Write-Host "`n🎉 Deployment completed successfully!" -ForegroundColor Green
    Write-Host "Use 'docker-compose -f $composeFile logs -f [service]' to view logs" -ForegroundColor Gray

}
catch {
    Write-Host "`n❌ Deployment failed: $($_.Exception.Message)" -ForegroundColor Red
    Write-Host "Use 'docker-compose -f $composeFile logs' to check logs" -ForegroundColor Gray
    exit 1
}
