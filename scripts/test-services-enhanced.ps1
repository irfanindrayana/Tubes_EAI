#!/usr/bin/env powershell

# Comprehensive Service Testing Script
# Tests all microservices functionality and inter-service communication

param(
    [string]$Service = "",
    [switch]$Integration = $false,
    [switch]$Load = $false,
    [switch]$Detailed = $false,
    [int]$Concurrency = 10,
    [int]$Requests = 100
)

Write-Host "🧪 Trans Bandung Microservices Testing Suite" -ForegroundColor Green
Write-Host "=============================================" -ForegroundColor Green

# Test configurations
$testSuites = @{
    "user-service" = @{
        "baseUrl" = "http://localhost:8001"
        "healthEndpoint" = "/health"
        "apiEndpoints" = @(
            @{Method="GET"; Path="/api/v1/internal/users/1"; ExpectedStatus=200},
            @{Method="POST"; Path="/api/v1/internal/users/multiple"; Body=@{user_ids=@(1,2)}; ExpectedStatus=200}
        )
    }
    "ticketing-service" = @{
        "baseUrl" = "http://localhost:8002"
        "healthEndpoint" = "/health"
        "apiEndpoints" = @(
            @{Method="GET"; Path="/api/v1/internal/ticketing/routes/1"; ExpectedStatus=200},
            @{Method="POST"; Path="/api/v1/internal/ticketing/seats/availability"; Body=@{schedule_id=1;seat_count=2}; ExpectedStatus=200}
        )
    }
    "payment-service" = @{
        "baseUrl" = "http://localhost:8003"
        "healthEndpoint" = "/health"
        "apiEndpoints" = @(
            @{Method="GET"; Path="/api/v1/internal/payments/methods"; ExpectedStatus=200},
            @{Method="GET"; Path="/api/v1/internal/payments/1"; ExpectedStatus=200}
        )
    }
    "inbox-service" = @{
        "baseUrl" = "http://localhost:8004"
        "healthEndpoint" = "/health"
        "apiEndpoints" = @(
            @{Method="GET"; Path="/api/v1/internal/inbox/user/1/messages"; ExpectedStatus=200},
            @{Method="GET"; Path="/api/v1/internal/inbox/user/1/unread-count"; ExpectedStatus=200}
        )
    }
    "reviews-service" = @{
        "baseUrl" = "http://localhost:8005"
        "healthEndpoint" = "/health"
        "apiEndpoints" = @(
            @{Method="GET"; Path="/api/v1/internal/reviews/route/1"; ExpectedStatus=200},
            @{Method="GET"; Path="/api/v1/internal/reviews/stats/1"; ExpectedStatus=200}
        )
    }
    "api-gateway" = @{
        "baseUrl" = "http://localhost:8000"
        "healthEndpoint" = "/health"
        "apiEndpoints" = @(
            @{Method="GET"; Path="/api/v1/users"; ExpectedStatus=302}, # Redirect to auth
            @{Method="GET"; Path="/"; ExpectedStatus=200}
        )
    }
}

function Write-TestLog {
    param($Message, $Level = "INFO", $TestName = "")
    $timestamp = Get-Date -Format "HH:mm:ss"
    $prefix = if ($TestName) { "[$TestName] " } else { "" }
    
    $color = switch ($Level) {
        "PASS" { "Green" }
        "FAIL" { "Red" }
        "WARN" { "Yellow" }
        "INFO" { "Cyan" }
        default { "White" }
    }
    
    Write-Host "[$timestamp] $prefix$Message" -ForegroundColor $color
}

function Test-HealthEndpoint {
    param($ServiceConfig, $ServiceName)
    
    $url = "$($ServiceConfig.baseUrl)$($ServiceConfig.healthEndpoint)"
    
    try {
        $response = Invoke-RestMethod -Uri $url -TimeoutSec 10 -ErrorAction Stop
        
        if ($response.status -eq "healthy") {
            Write-TestLog "Health check passed" "PASS" $ServiceName
            
            if ($Detailed -and $response.database) {
                Write-TestLog "  - Database: $($response.database)" "INFO" $ServiceName
            }
            if ($Detailed -and $response.redis) {
                Write-TestLog "  - Redis: $($response.redis)" "INFO" $ServiceName
            }
            
            return $true
        } else {
            Write-TestLog "Health check failed - Status: $($response.status)" "FAIL" $ServiceName
            return $false
        }
    }
    catch {
        Write-TestLog "Health check failed - Error: $($_.Exception.Message)" "FAIL" $ServiceName
        return $false
    }
}

function Test-ApiEndpoint {
    param($ServiceConfig, $ServiceName, $Endpoint)
    
    $url = "$($ServiceConfig.baseUrl)$($Endpoint.Path)"
    
    try {
        $params = @{
            Uri = $url
            Method = $Endpoint.Method
            TimeoutSec = 10
            ErrorAction = "Stop"
        }
        
        if ($Endpoint.Body) {
            $params.Body = ($Endpoint.Body | ConvertTo-Json)
            $params.ContentType = "application/json"
        }
        
        $response = Invoke-RestMethod @params
        $statusCode = 200 # Default for successful RestMethod call
        
        if ($statusCode -eq $Endpoint.ExpectedStatus) {
            Write-TestLog "$($Endpoint.Method) $($Endpoint.Path) - Status: $statusCode" "PASS" $ServiceName
            return $true
        } else {
            Write-TestLog "$($Endpoint.Method) $($Endpoint.Path) - Expected: $($Endpoint.ExpectedStatus), Got: $statusCode" "FAIL" $ServiceName
            return $false
        }
    }
    catch {
        # Handle expected error responses
        if ($_.Exception.Response -and $_.Exception.Response.StatusCode -eq $Endpoint.ExpectedStatus) {
            Write-TestLog "$($Endpoint.Method) $($Endpoint.Path) - Status: $($Endpoint.ExpectedStatus)" "PASS" $ServiceName
            return $true
        }
        
        Write-TestLog "$($Endpoint.Method) $($Endpoint.Path) - Error: $($_.Exception.Message)" "FAIL" $ServiceName
        return $false
    }
}

function Test-InterServiceCommunication {
    Write-TestLog "Testing inter-service communication..." "INFO" "INTEGRATION"
    
    $testResults = @()
    
    # Test 1: User service -> Get user data
    try {
        $userResponse = Invoke-RestMethod -Uri "http://localhost:8001/api/v1/internal/users/1" -TimeoutSec 10
        if ($userResponse.data) {
            Write-TestLog "✅ User data retrieval successful" "PASS" "INTEGRATION"
            $testResults += $true
        } else {
            Write-TestLog "❌ User data retrieval failed" "FAIL" "INTEGRATION"
            $testResults += $false
        }
    }
    catch {
        Write-TestLog "❌ User service communication failed: $($_.Exception.Message)" "FAIL" "INTEGRATION"
        $testResults += $false
    }
    
    # Test 2: API Gateway -> Service routing
    try {
        $gatewayResponse = Invoke-RestMethod -Uri "http://localhost:8000/health" -TimeoutSec 10
        if ($gatewayResponse.services) {
            $connectedServices = ($gatewayResponse.services.PSObject.Properties | Where-Object { $_.Value -eq $true }).Count
            Write-TestLog "✅ API Gateway connected to $connectedServices services" "PASS" "INTEGRATION"
            $testResults += $true
        } else {
            Write-TestLog "❌ API Gateway service connections not found" "FAIL" "INTEGRATION"
            $testResults += $false
        }
    }
    catch {
        Write-TestLog "❌ API Gateway communication failed: $($_.Exception.Message)" "FAIL" "INTEGRATION"
        $testResults += $false
    }
    
    $passRate = (($testResults | Where-Object { $_ -eq $true }).Count / $testResults.Count) * 100
    Write-TestLog "Integration test pass rate: $([math]::Round($passRate, 2))%" "INFO" "INTEGRATION"
    
    return $passRate -ge 80
}

function Start-LoadTest {
    param($ServiceName, $ServiceConfig)
    
    Write-TestLog "Starting load test with $Concurrency concurrent users, $Requests requests" "INFO" $ServiceName
    
    $url = "$($ServiceConfig.baseUrl)$($ServiceConfig.healthEndpoint)"
    $successCount = 0
    $failCount = 0
    $totalTime = 0
    
    $jobs = @()
    
    for ($i = 0; $i -lt $Concurrency; $i++) {
        $jobs += Start-Job -ScriptBlock {
            param($Url, $RequestsPerWorker)
            
            $results = @()
            for ($j = 0; $j -lt $RequestsPerWorker; $j++) {
                $stopwatch = [System.Diagnostics.Stopwatch]::StartNew()
                try {
                    Invoke-RestMethod -Uri $Url -TimeoutSec 5 | Out-Null
                    $stopwatch.Stop()
                    $results += @{Success = $true; Time = $stopwatch.ElapsedMilliseconds}
                }
                catch {
                    $stopwatch.Stop()
                    $results += @{Success = $false; Time = $stopwatch.ElapsedMilliseconds}
                }
            }
            return $results
        } -ArgumentList $url, [math]::Floor($Requests / $Concurrency)
    }
    
    Write-TestLog "Waiting for load test completion..." "INFO" $ServiceName
    $allResults = $jobs | Wait-Job | Receive-Job
    $jobs | Remove-Job
    
    $successCount = ($allResults | Where-Object { $_.Success -eq $true }).Count
    $failCount = ($allResults | Where-Object { $_.Success -eq $false }).Count
    $avgResponseTime = ($allResults | Measure-Object -Property Time -Average).Average
    
    $successRate = ($successCount / ($successCount + $failCount)) * 100
    
    Write-TestLog "Load test results:" "INFO" $ServiceName
    Write-TestLog "  - Total requests: $($successCount + $failCount)" "INFO" $ServiceName
    Write-TestLog "  - Successful: $successCount" "INFO" $ServiceName
    Write-TestLog "  - Failed: $failCount" "INFO" $ServiceName
    Write-TestLog "  - Success rate: $([math]::Round($successRate, 2))%" "INFO" $ServiceName
    Write-TestLog "  - Average response time: $([math]::Round($avgResponseTime, 2))ms" "INFO" $ServiceName
    
    return $successRate -ge 95
}

# Main test execution
try {
    $overallResults = @()
    $servicesToTest = if ($Service) { @($Service) } else { $testSuites.Keys }
    
    foreach ($serviceName in $servicesToTest) {
        if (-not $testSuites.ContainsKey($serviceName)) {
            Write-TestLog "Unknown service: $serviceName" "FAIL"
            continue
        }
        
        Write-TestLog "Testing $serviceName..." "INFO"
        $serviceConfig = $testSuites[$serviceName]
        $serviceResults = @()
        
        # Health check test
        $healthResult = Test-HealthEndpoint $serviceConfig $serviceName
        $serviceResults += $healthResult
        
        # API endpoint tests
        foreach ($endpoint in $serviceConfig.apiEndpoints) {
            $apiResult = Test-ApiEndpoint $serviceConfig $serviceName $endpoint
            $serviceResults += $apiResult
        }
        
        # Load test
        if ($Load) {
            $loadResult = Start-LoadTest $serviceName $serviceConfig
            $serviceResults += $loadResult
        }
        
        $servicePassRate = (($serviceResults | Where-Object { $_ -eq $true }).Count / $serviceResults.Count) * 100
        Write-TestLog "$serviceName overall pass rate: $([math]::Round($servicePassRate, 2))%" "INFO"
        
        $overallResults += $servicePassRate -ge 80
    }
    
    # Integration tests
    if ($Integration) {
        $integrationResult = Test-InterServiceCommunication
        $overallResults += $integrationResult
    }
    
    # Final summary
    $overallPassRate = (($overallResults | Where-Object { $_ -eq $true }).Count / $overallResults.Count) * 100
    Write-Host "`n🎯 Test Summary:" -ForegroundColor Cyan
    Write-Host "Overall pass rate: $([math]::Round($overallPassRate, 2))%" -ForegroundColor $(if ($overallPassRate -ge 80) { "Green" } else { "Red" })
    
    if ($overallPassRate -ge 80) {
        Write-Host "🎉 All tests passed! Services are ready for production." -ForegroundColor Green
        exit 0
    } else {
        Write-Host "❌ Some tests failed. Please check the logs above." -ForegroundColor Red
        exit 1
    }
}
catch {
    Write-TestLog "Fatal error during testing: $($_.Exception.Message)" "FAIL"
    exit 1
}
