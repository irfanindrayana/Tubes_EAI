#!/usr/bin/env powershell

# Service Health Check and Monitoring Script
# Comprehensive health monitoring for all microservices

param(
    [string]$Service = "",
    [switch]$Continuous = $false,
    [int]$Interval = 30,
    [switch]$Detailed = $false
)

Write-Host "🏥 Trans Bandung Microservices Health Monitor" -ForegroundColor Green
Write-Host "=============================================" -ForegroundColor Green

# Service configurations
$services = @(
    @{Name="user-service"; Port=8001; HealthEndpoint="/health"},
    @{Name="ticketing-service"; Port=8002; HealthEndpoint="/health"},
    @{Name="payment-service"; Port=8003; HealthEndpoint="/health"},
    @{Name="inbox-service"; Port=8004; HealthEndpoint="/health"},
    @{Name="reviews-service"; Port=8005; HealthEndpoint="/health"},
    @{Name="api-gateway"; Port=8000; HealthEndpoint="/health"},
    @{Name="nginx-lb"; Port=80; HealthEndpoint="/health"}
)

function Test-ServiceHealth {
    param($ServiceConfig)
    
    $url = "http://localhost:$($ServiceConfig.Port)$($ServiceConfig.HealthEndpoint)"
    
    try {
        $response = Invoke-RestMethod -Uri $url -TimeoutSec 5 -ErrorAction Stop
        
        $status = if ($response.status -eq "healthy") { "✅ HEALTHY" } else { "⚠️  DEGRADED" }
        $color = if ($response.status -eq "healthy") { "Green" } else { "Yellow" }
        
        Write-Host "[$($ServiceConfig.Name.PadRight(18))] $status" -ForegroundColor $color
        
        if ($Detailed -and $response) {
            Write-Host "  Database: $($response.database)" -ForegroundColor Gray
            Write-Host "  Redis: $($response.redis)" -ForegroundColor Gray
            Write-Host "  Timestamp: $($response.timestamp)" -ForegroundColor Gray
            if ($response.services) {
                Write-Host "  Connected Services:" -ForegroundColor Gray
                $response.services.PSObject.Properties | ForEach-Object {
                    $serviceStatus = if ($_.Value) { "✅" } else { "❌" }
                    Write-Host "    $($_.Name): $serviceStatus" -ForegroundColor Gray
                }
            }
            Write-Host ""
        }
        
        return $true
    }
    catch {
        Write-Host "[$($ServiceConfig.Name.PadRight(18))] ❌ UNHEALTHY - $($_.Exception.Message)" -ForegroundColor Red
        return $false
    }
}

function Get-ContainerStats {
    Write-Host "`n📊 Container Resource Usage:" -ForegroundColor Cyan
    docker stats --no-stream --format "table {{.Name}}\t{{.CPUPerc}}\t{{.MemUsage}}\t{{.NetIO}}\t{{.BlockIO}}"
}

function Get-ServiceLogs {
    param($ServiceName, $Lines = 20)
    
    Write-Host "`n📋 Recent logs for $ServiceName (last $Lines lines):" -ForegroundColor Cyan
    docker-compose -f docker-compose.services.yml logs --tail=$Lines $ServiceName
}

do {
    Clear-Host
    Write-Host "🏥 Health Check Report - $(Get-Date)" -ForegroundColor Green
    Write-Host "=" * 50 -ForegroundColor Green
    
    $healthyCount = 0
    $totalCount = $services.Count
    
    if ($Service -ne "") {
        $serviceToCheck = $services | Where-Object { $_.Name -eq $Service }
        if ($serviceToCheck) {
            $services = @($serviceToCheck)
            $totalCount = 1
        } else {
            Write-Host "❌ Service '$Service' not found" -ForegroundColor Red
            exit 1
        }
    }
    
    foreach ($serviceConfig in $services) {
        if (Test-ServiceHealth $serviceConfig) {
            $healthyCount++
        }
    }
    
    Write-Host "`n📈 Overall Health: $healthyCount/$totalCount services healthy" -ForegroundColor $(if ($healthyCount -eq $totalCount) { "Green" } else { "Yellow" })
    
    if ($Detailed) {
        Get-ContainerStats
    }
    
    if ($Service -ne "" -and $Detailed) {
        Get-ServiceLogs $Service
    }
    
    if ($Continuous) {
        Write-Host "`n⏱️  Next check in $Interval seconds... (Press Ctrl+C to stop)" -ForegroundColor Gray
        Start-Sleep -Seconds $Interval
    }
    
} while ($Continuous)

if ($healthyCount -lt $totalCount) {
    exit 1
}
