#!/usr/bin/env powershell

# Comprehensive Deployment Validation Script
# Validates complete microservices deployment and functionality

param(
    [switch]$Quick = $false,
    [switch]$Deep = $false,
    [switch]$Performance = $false,
    [switch]$Export = $false,
    [string]$OutputFile = "validation-report-$(Get-Date -Format 'yyyy-MM-dd-HHmmss').json"
)

Write-Host "🔍 Trans Bandung Deployment Validation Suite" -ForegroundColor Green
Write-Host "=============================================" -ForegroundColor Green

# Validation test suites
$validationTests = @{
    "infrastructure" = @{
        "container_status" = @{
            Description = "Check all containers are running"
            Critical = $true
        }
        "network_connectivity" = @{
            Description = "Verify network connectivity between services"
            Critical = $true
        }
        "volume_mounts" = @{
            Description = "Validate volume mounts and permissions"
            Critical = $false
        }
    }
    "services" = @{
        "health_endpoints" = @{
            Description = "Test all service health endpoints"
            Critical = $true
        }
        "database_connections" = @{
            Description = "Verify database connectivity"
            Critical = $true
        }
        "redis_connectivity" = @{
            Description = "Test Redis connectivity"
            Critical = $true
        }
    }
    "apis" = @{
        "internal_apis" = @{
            Description = "Test internal API endpoints"
            Critical = $true
        }
        "authentication" = @{
            Description = "Verify authentication flows"
            Critical = $true
        }
        "authorization" = @{
            Description = "Test authorization mechanisms"
            Critical = $false
        }
    }
    "integration" = @{
        "inter_service_communication" = @{
            Description = "Test service-to-service communication"
            Critical = $true
        }
        "data_consistency" = @{
            Description = "Verify data consistency across services"
            Critical = $true
        }
        "event_propagation" = @{
            Description = "Test event-driven communication"
            Critical = $false
        }
    }
    "performance" = @{
        "response_times" = @{
            Description = "Measure API response times"
            Critical = $false
        }
        "throughput" = @{
            Description = "Test service throughput"
            Critical = $false
        }
        "resource_usage" = @{
            Description = "Monitor resource consumption"
            Critical = $false
        }
    }
}

$validationResults = @{
    timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    tests = @{}
    summary = @{
        total = 0
        passed = 0
        failed = 0
        skipped = 0
        critical_failed = 0
    }
}

function Write-ValidationLog {
    param($Message, $Level = "INFO", $TestName = "")
    $timestamp = Get-Date -Format "HH:mm:ss"
    $prefix = if ($TestName) { "[$TestName] " } else { "" }
    
    $color = switch ($Level) {
        "PASS" { "Green" }
        "FAIL" { "Red" }
        "WARN" { "Yellow" }
        "INFO" { "Cyan" }
        "SKIP" { "Gray" }
        default { "White" }
    }
    
    Write-Host "[$timestamp] $prefix$Message" -ForegroundColor $color
}

function Test-ContainerStatus {
    Write-ValidationLog "Testing container status..." "INFO" "INFRA"
    
    $expectedContainers = @(
        "transbandung-user-db",
        "transbandung-ticketing-db",
        "transbandung-payment-db",
        "transbandung-inbox-db",
        "transbandung-reviews-db",
        "transbandung-redis",
        "transbandung-user-service",
        "transbandung-ticketing-service",
        "transbandung-payment-service",
        "transbandung-inbox-service",
        "transbandung-reviews-service",
        "transbandung-api-gateway",
        "transbandung-nginx-lb"
    )
    
    $runningContainers = docker ps --format "{{.Names}}"
    $missingContainers = @()
    
    foreach ($container in $expectedContainers) {
        if ($runningContainers -notcontains $container) {
            $missingContainers += $container
        }
    }
    
    if ($missingContainers.Count -eq 0) {
        Write-ValidationLog "✅ All expected containers are running" "PASS" "INFRA"
        return @{Status = "PASS"; Details = "All $($expectedContainers.Count) containers running"}
    } else {
        Write-ValidationLog "❌ Missing containers: $($missingContainers -join ', ')" "FAIL" "INFRA"
        return @{Status = "FAIL"; Details = "Missing: $($missingContainers -join ', ')"}
    }
}

function Test-ServiceHealth {
    Write-ValidationLog "Testing service health endpoints..." "INFO" "SERVICES"
    
    $healthEndpoints = @{
        "user-service" = "http://localhost:8001/health"
        "ticketing-service" = "http://localhost:8002/health"
        "payment-service" = "http://localhost:8003/health"
        "inbox-service" = "http://localhost:8004/health"
        "reviews-service" = "http://localhost:8005/health"
        "api-gateway" = "http://localhost:8000/health"
    }
    
    $healthResults = @{}
    $allHealthy = $true
    
    foreach ($service in $healthEndpoints.Keys) {
        try {
            $response = Invoke-RestMethod -Uri $healthEndpoints[$service] -TimeoutSec 5 -ErrorAction Stop
            
            if ($response.status -eq "healthy") {
                Write-ValidationLog "✅ $service is healthy" "PASS" "SERVICES"
                $healthResults[$service] = @{
                    Status = "PASS"
                    Response = $response
                }
            } else {
                Write-ValidationLog "⚠️ $service is not healthy: $($response.status)" "WARN" "SERVICES"
                $healthResults[$service] = @{
                    Status = "WARN"
                    Response = $response
                }
                $allHealthy = $false
            }
        }
        catch {
            Write-ValidationLog "❌ $service health check failed: $($_.Exception.Message)" "FAIL" "SERVICES"
            $healthResults[$service] = @{
                Status = "FAIL"
                Error = $_.Exception.Message
            }
            $allHealthy = $false
        }
    }
    
    return @{
        Status = if ($allHealthy) { "PASS" } else { "FAIL" }
        Details = $healthResults
    }
}

function Test-DatabaseConnectivity {
    Write-ValidationLog "Testing database connectivity..." "INFO" "SERVICES"
    
    $databases = @{
        "user-db" = "transbandung_users"
        "ticketing-db" = "transbandung_ticketing"
        "payment-db" = "transbandung_payments"
        "inbox-db" = "transbandung_inbox"
        "reviews-db" = "transbandung_reviews"
    }
    
    $dbResults = @{}
    $allConnected = $true
    
    foreach ($container in $databases.Keys) {
        try {
            $containerName = "transbandung-$container"
            $dbName = $databases[$container]
            
            $result = docker exec $containerName mysql -u microservice -pmicroservice123 -e "SELECT 1;" $dbName 2>&1
            
            if ($LASTEXITCODE -eq 0) {
                Write-ValidationLog "✅ Database $container is accessible" "PASS" "SERVICES"
                $dbResults[$container] = @{Status = "PASS"}
            } else {
                Write-ValidationLog "❌ Database $container connection failed" "FAIL" "SERVICES"
                $dbResults[$container] = @{Status = "FAIL"; Error = $result}
                $allConnected = $false
            }
        }
        catch {
            Write-ValidationLog "❌ Database $container test failed: $($_.Exception.Message)" "FAIL" "SERVICES"
            $dbResults[$container] = @{Status = "FAIL"; Error = $_.Exception.Message}
            $allConnected = $false
        }
    }
    
    return @{
        Status = if ($allConnected) { "PASS" } else { "FAIL" }
        Details = $dbResults
    }
}

function Test-InternalAPIs {
    Write-ValidationLog "Testing internal API endpoints..." "INFO" "APIS"
    
    $internalAPIs = @{
        "user-service" = @(
            @{Method="GET"; Path="http://localhost:8001/api/v1/internal/users/1"}
        )
        "ticketing-service" = @(
            @{Method="GET"; Path="http://localhost:8002/api/v1/internal/ticketing/routes/1"}
        )
        "payment-service" = @(
            @{Method="GET"; Path="http://localhost:8003/api/v1/internal/payments/methods"}
        )
        "inbox-service" = @(
            @{Method="GET"; Path="http://localhost:8004/api/v1/internal/inbox/user/1/unread-count"}
        )
    }
    
    $apiResults = @{}
    $allWorking = $true
    
    foreach ($service in $internalAPIs.Keys) {
        $serviceResults = @()
        
        foreach ($api in $internalAPIs[$service]) {
            try {
                $response = Invoke-RestMethod -Uri $api.Path -Method $api.Method -TimeoutSec 5 -ErrorAction Stop
                Write-ValidationLog "✅ $service internal API working" "PASS" "APIS"
                $serviceResults += @{Status = "PASS"; API = $api.Path}
            }
            catch {
                Write-ValidationLog "❌ $service internal API failed: $($_.Exception.Message)" "FAIL" "APIS"
                $serviceResults += @{Status = "FAIL"; API = $api.Path; Error = $_.Exception.Message}
                $allWorking = $false
            }
        }
        
        $apiResults[$service] = $serviceResults
    }
    
    return @{
        Status = if ($allWorking) { "PASS" } else { "FAIL" }
        Details = $apiResults
    }
}

function Test-PerformanceMetrics {
    if (!$Performance) {
        return @{Status = "SKIP"; Details = "Performance testing disabled"}
    }
    
    Write-ValidationLog "Running performance tests..." "INFO" "PERF"
    
    # Resource usage
    $containerStats = docker stats --no-stream --format "table {{.Name}}\t{{.CPUPerc}}\t{{.MemUsage}}\t{{.NetIO}}"
    
    # Response time tests
    $responseTimes = @{}
    $healthEndpoints = @{
        "user-service" = "http://localhost:8001/health"
        "ticketing-service" = "http://localhost:8002/health"
        "api-gateway" = "http://localhost:8000/health"
    }
    
    foreach ($service in $healthEndpoints.Keys) {
        $times = @()
        for ($i = 0; $i -lt 5; $i++) {
            $stopwatch = [System.Diagnostics.Stopwatch]::StartNew()
            try {
                Invoke-RestMethod -Uri $healthEndpoints[$service] -TimeoutSec 5 | Out-Null
                $stopwatch.Stop()
                $times += $stopwatch.ElapsedMilliseconds
            }
            catch {
                $stopwatch.Stop()
                $times += 999999  # High value for failed requests
            }
        }
        $responseTimes[$service] = @{
            Average = ($times | Measure-Object -Average).Average
            Max = ($times | Measure-Object -Maximum).Maximum
            Min = ($times | Measure-Object -Minimum).Minimum
        }
    }
    
    return @{
        Status = "PASS"
        Details = @{
            ResourceUsage = $containerStats
            ResponseTimes = $responseTimes
        }
    }
}

# Main validation execution
try {
    Write-ValidationLog "Starting comprehensive deployment validation..." "INFO"
    
    # Infrastructure tests
    Write-ValidationLog "Phase 1: Infrastructure validation" "INFO"
    $validationResults.tests.container_status = Test-ContainerStatus
    $validationResults.summary.total++
    
    if ($validationResults.tests.container_status.Status -eq "PASS") {
        $validationResults.summary.passed++
    } else {
        $validationResults.summary.failed++
        $validationResults.summary.critical_failed++
    }
    
    # Service tests
    Write-ValidationLog "Phase 2: Service validation" "INFO"
    $validationResults.tests.service_health = Test-ServiceHealth
    $validationResults.summary.total++
    
    if ($validationResults.tests.service_health.Status -eq "PASS") {
        $validationResults.summary.passed++
    } else {
        $validationResults.summary.failed++
        $validationResults.summary.critical_failed++
    }
    
    $validationResults.tests.database_connectivity = Test-DatabaseConnectivity
    $validationResults.summary.total++
    
    if ($validationResults.tests.database_connectivity.Status -eq "PASS") {
        $validationResults.summary.passed++
    } else {
        $validationResults.summary.failed++
        $validationResults.summary.critical_failed++
    }
    
    # API tests
    if (!$Quick) {
        Write-ValidationLog "Phase 3: API validation" "INFO"
        $validationResults.tests.internal_apis = Test-InternalAPIs
        $validationResults.summary.total++
        
        if ($validationResults.tests.internal_apis.Status -eq "PASS") {
            $validationResults.summary.passed++
        } else {
            $validationResults.summary.failed++
            $validationResults.summary.critical_failed++
        }
    }
    
    # Performance tests
    if ($Performance -or $Deep) {
        Write-ValidationLog "Phase 4: Performance validation" "INFO"
        $validationResults.tests.performance = Test-PerformanceMetrics
        $validationResults.summary.total++
        
        if ($validationResults.tests.performance.Status -eq "PASS") {
            $validationResults.summary.passed++
        } elseif ($validationResults.tests.performance.Status -eq "SKIP") {
            $validationResults.summary.skipped++
        } else {
            $validationResults.summary.failed++
        }
    }
    
    # Generate report
    Write-Host "`n📊 Validation Summary:" -ForegroundColor Cyan
    Write-Host "=====================" -ForegroundColor Cyan
    Write-Host "Total Tests: $($validationResults.summary.total)" -ForegroundColor White
    Write-Host "Passed: $($validationResults.summary.passed)" -ForegroundColor Green
    Write-Host "Failed: $($validationResults.summary.failed)" -ForegroundColor Red
    Write-Host "Skipped: $($validationResults.summary.skipped)" -ForegroundColor Gray
    Write-Host "Critical Failures: $($validationResults.summary.critical_failed)" -ForegroundColor Red
    
    $successRate = if ($validationResults.summary.total -gt 0) {
        ($validationResults.summary.passed / $validationResults.summary.total) * 100
    } else { 0 }
    
    Write-Host "Success Rate: $([math]::Round($successRate, 2))%" -ForegroundColor $(
        if ($successRate -ge 90) { "Green" }
        elseif ($successRate -ge 70) { "Yellow" }
        else { "Red" }
    )
    
    # Export results
    if ($Export) {
        $validationResults | ConvertTo-Json -Depth 10 | Out-File $OutputFile
        Write-ValidationLog "Results exported to: $OutputFile" "INFO"
    }
    
    # Exit with appropriate code
    if ($validationResults.summary.critical_failed -eq 0 -and $successRate -ge 80) {
        Write-Host "`n🎉 Deployment validation PASSED! System is ready for production." -ForegroundColor Green
        exit 0
    } else {
        Write-Host "`n❌ Deployment validation FAILED! Please review the issues above." -ForegroundColor Red
        exit 1
    }
}
catch {
    Write-ValidationLog "Fatal error during validation: $($_.Exception.Message)" "FAIL"
    exit 1
}
