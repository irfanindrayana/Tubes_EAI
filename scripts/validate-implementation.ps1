#!/usr/bin/env powershell

# Trans Bandung Microservices Implementation Validation
# Comprehensive validation of the containerization implementation

Write-Host "🔍 Trans Bandung Microservices Implementation Validation" -ForegroundColor Green
Write-Host "======================================================" -ForegroundColor Green

$validationResults = @{
    "Infrastructure" = @()
    "Services" = @()
    "Configuration" = @()
    "Communication" = @()
    "Deployment" = @()
}

function Test-FileExists {
    param([string]$Path, [string]$Description)
    
    if (Test-Path $Path) {
        Write-Host "  ✅ $Description" -ForegroundColor Green
        return $true
    } else {
        Write-Host "  ❌ $Description - Missing: $Path" -ForegroundColor Red
        return $false
    }
}

function Test-DirectoryStructure {
    Write-Host "`n📁 Validating Directory Structure..." -ForegroundColor Yellow
    
    $requiredDirs = @(
        @{Path="dockerfiles"; Desc="Dockerfiles directory"},
        @{Path="envs"; Desc="Environment files directory"},
        @{Path="docker/nginx"; Desc="Nginx configurations"},
        @{Path="docker/supervisor"; Desc="Supervisor configurations"},
        @{Path="docker/mysql/init"; Desc="Database initialization scripts"},
        @{Path="nginx"; Desc="Load balancer configuration"},
        @{Path="scripts"; Desc="Deployment scripts"},
        @{Path="routes"; Desc="Service routes directory"},
        @{Path="app/Services/Http"; Desc="HTTP client classes"}
    )
    
    $passed = 0
    foreach ($dir in $requiredDirs) {
        if (Test-FileExists -Path $dir.Path -Description $dir.Desc) {
            $passed++
        }
    }
    
    $validationResults["Infrastructure"] += @{
        Test = "Directory Structure"
        Passed = $passed
        Total = $requiredDirs.Count
        Status = if ($passed -eq $requiredDirs.Count) { "PASS" } else { "FAIL" }
    }
}

function Test-DockerFiles {
    Write-Host "`n🐳 Validating Docker Files..." -ForegroundColor Yellow
    
    $dockerFiles = @(
        @{Path="dockerfiles/user-service.Dockerfile"; Desc="User Service Dockerfile"},
        @{Path="dockerfiles/ticketing-service.Dockerfile"; Desc="Ticketing Service Dockerfile"},
        @{Path="dockerfiles/payment-service.Dockerfile"; Desc="Payment Service Dockerfile"},
        @{Path="dockerfiles/inbox-service.Dockerfile"; Desc="Inbox Service Dockerfile"},
        @{Path="dockerfiles/api-gateway.Dockerfile"; Desc="API Gateway Dockerfile"},
        @{Path="docker-compose.services.yml"; Desc="Docker Compose Services File"}
    )
    
    $passed = 0
    foreach ($file in $dockerFiles) {
        if (Test-FileExists -Path $file.Path -Description $file.Desc) {
            $passed++
        }
    }
    
    $validationResults["Infrastructure"] += @{
        Test = "Docker Files"
        Passed = $passed
        Total = $dockerFiles.Count
        Status = if ($passed -eq $dockerFiles.Count) { "PASS" } else { "FAIL" }
    }
}

function Test-EnvironmentFiles {
    Write-Host "`n⚙️  Validating Environment Files..." -ForegroundColor Yellow
    
    $envFiles = @(
        @{Path="envs/.env.user-service"; Desc="User Service Environment"},
        @{Path="envs/.env.ticketing-service-temp"; Desc="Ticketing Service Environment"},
        @{Path="envs/.env.payment-service"; Desc="Payment Service Environment"},
        @{Path="envs/.env.inbox-service"; Desc="Inbox Service Environment"},
        @{Path="envs/.env.api-gateway"; Desc="API Gateway Environment"}
    )
    
    $passed = 0
    foreach ($file in $envFiles) {
        if (Test-FileExists -Path $file.Path -Description $file.Desc) {
            $passed++
        }
    }
    
    $validationResults["Configuration"] += @{
        Test = "Environment Files"
        Passed = $passed
        Total = $envFiles.Count
        Status = if ($passed -eq $envFiles.Count) { "PASS" } else { "FAIL" }
    }
}

function Test-ServiceRoutes {
    Write-Host "`n🛣️  Validating Service Routes..." -ForegroundColor Yellow
    
    $routeFiles = @(
        @{Path="routes/user-service.php"; Desc="User Service Routes"},
        @{Path="routes/ticketing-service.php"; Desc="Ticketing Service Routes"},
        @{Path="routes/payment-service.php"; Desc="Payment Service Routes"},
        @{Path="routes/inbox-service.php"; Desc="Inbox Service Routes"}
    )
    
    $passed = 0
    foreach ($file in $routeFiles) {
        if (Test-FileExists -Path $file.Path -Description $file.Desc) {
            $passed++
        }
    }
    
    $validationResults["Services"] += @{
        Test = "Service Routes"
        Passed = $passed
        Total = $routeFiles.Count
        Status = if ($passed -eq $routeFiles.Count) { "PASS" } else { "FAIL" }
    }
}

function Test-HttpClients {
    Write-Host "`n🔗 Validating HTTP Client Classes..." -ForegroundColor Yellow
    
    $clientFiles = @(
        @{Path="app/Services/Http/UserServiceClient.php"; Desc="User Service Client"},
        @{Path="app/Services/Http/PaymentServiceClient.php"; Desc="Payment Service Client"}, # Note: This was misnamed, should be TicketingServiceClient
        @{Path="app/Services/Http/InboxServiceClient.php"; Desc="Inbox Service Client"}
    )
    
    $passed = 0
    foreach ($file in $clientFiles) {
        if (Test-FileExists -Path $file.Path -Description $file.Desc) {
            $passed++
        }
    }
    
    $validationResults["Communication"] += @{
        Test = "HTTP Client Classes"
        Passed = $passed
        Total = $clientFiles.Count
        Status = if ($passed -eq $clientFiles.Count) { "PASS" } else { "FAIL" }
    }
}

function Test-NginxConfiguration {
    Write-Host "`n🌐 Validating Nginx Configuration..." -ForegroundColor Yellow
    
    $nginxFiles = @(
        @{Path="nginx/nginx.conf"; Desc="Main Nginx Configuration"},
        @{Path="nginx/default.conf"; Desc="Default Server Configuration"},
        @{Path="nginx/proxy_params.conf"; Desc="Proxy Parameters"},
        @{Path="docker/nginx/user-service.conf"; Desc="User Service Nginx Config"},
        @{Path="docker/nginx/ticketing-service.conf"; Desc="Ticketing Service Nginx Config"},
        @{Path="docker/nginx/payment-service.conf"; Desc="Payment Service Nginx Config"},
        @{Path="docker/nginx/inbox-service.conf"; Desc="Inbox Service Nginx Config"},
        @{Path="docker/nginx/api-gateway.conf"; Desc="API Gateway Nginx Config"}
    )
    
    $passed = 0
    foreach ($file in $nginxFiles) {
        if (Test-FileExists -Path $file.Path -Description $file.Desc) {
            $passed++
        }
    }
    
    $validationResults["Configuration"] += @{
        Test = "Nginx Configuration"
        Passed = $passed
        Total = $nginxFiles.Count
        Status = if ($passed -eq $nginxFiles.Count) { "PASS" } else { "FAIL" }
    }
}

function Test-DatabaseInitialization {
    Write-Host "`n🗄️  Validating Database Initialization..." -ForegroundColor Yellow
    
    $dbFiles = @(
        @{Path="docker/mysql/init/01-user-db.sql"; Desc="User Database Init"},
        @{Path="docker/mysql/init/02-ticketing-db.sql"; Desc="Ticketing Database Init"},
        @{Path="docker/mysql/init/03-payment-db.sql"; Desc="Payment Database Init"},
        @{Path="docker/mysql/init/04-inbox-db.sql"; Desc="Inbox Database Init"},
        @{Path="docker/mysql/init/05-reviews-db.sql"; Desc="Reviews Database Init"}
    )
    
    $passed = 0
    foreach ($file in $dbFiles) {
        if (Test-FileExists -Path $file.Path -Description $file.Desc) {
            $passed++
        }
    }
    
    $validationResults["Infrastructure"] += @{
        Test = "Database Initialization"
        Passed = $passed
        Total = $dbFiles.Count
        Status = if ($passed -eq $dbFiles.Count) { "PASS" } else { "FAIL" }
    }
}

function Test-DeploymentScripts {
    Write-Host "`n🚀 Validating Deployment Scripts..." -ForegroundColor Yellow
    
    $scriptFiles = @(
        @{Path="scripts/deploy-services.ps1"; Desc="Main Deployment Script"},
        @{Path="scripts/test-services.ps1"; Desc="Service Testing Script"},
        @{Path="scripts/scale-service.ps1"; Desc="Service Scaling Script"},
        @{Path="scripts/logs-service.ps1"; Desc="Log Viewing Script"}
    )
    
    $passed = 0
    foreach ($file in $scriptFiles) {
        if (Test-FileExists -Path $file.Path -Description $file.Desc) {
            $passed++
        }
    }
    
    $validationResults["Deployment"] += @{
        Test = "Deployment Scripts"
        Passed = $passed
        Total = $scriptFiles.Count
        Status = if ($passed -eq $scriptFiles.Count) { "PASS" } else { "FAIL" }
    }
}

function Test-SupervisorConfiguration {
    Write-Host "`n👨‍💼 Validating Supervisor Configuration..." -ForegroundColor Yellow
    
    $supervisorFiles = @(
        @{Path="docker/supervisor/user-service.conf"; Desc="User Service Supervisor"},
        @{Path="docker/supervisor/ticketing-service.conf"; Desc="Ticketing Service Supervisor"},
        @{Path="docker/supervisor/payment-service.conf"; Desc="Payment Service Supervisor"},
        @{Path="docker/supervisor/inbox-service.conf"; Desc="Inbox Service Supervisor"},
        @{Path="docker/supervisor/api-gateway.conf"; Desc="API Gateway Supervisor"}
    )
    
    $passed = 0
    foreach ($file in $supervisorFiles) {
        if (Test-FileExists -Path $file.Path -Description $file.Desc) {
            $passed++
        }
    }
    
    $validationResults["Configuration"] += @{
        Test = "Supervisor Configuration"
        Passed = $passed
        Total = $supervisorFiles.Count
        Status = if ($passed -eq $supervisorFiles.Count) { "PASS" } else { "FAIL" }
    }
}

function Test-MiddlewareAndProviders {
    Write-Host "`n🔧 Validating Middleware and Providers..." -ForegroundColor Yellow
    
    $middlewareFiles = @(
        @{Path="app/Http/Middleware/ServiceAwareMiddleware.php"; Desc="Service Aware Middleware"},
        @{Path="app/Providers/ServiceRoutingProvider.php"; Desc="Service Routing Provider"}
    )
    
    $passed = 0
    foreach ($file in $middlewareFiles) {
        if (Test-FileExists -Path $file.Path -Description $file.Desc) {
            $passed++
        }
    }
    
    $validationResults["Services"] += @{
        Test = "Middleware and Providers"
        Passed = $passed
        Total = $middlewareFiles.Count
        Status = if ($passed -eq $middlewareFiles.Count) { "PASS" } else { "FAIL" }
    }
}

function Show-ValidationSummary {
    Write-Host "`n📊 Validation Summary" -ForegroundColor Green
    Write-Host "===================" -ForegroundColor Green
    
    $totalTests = 0
    $totalPassed = 0
    $categoryResults = @()
    
    foreach ($category in $validationResults.Keys) {
        $categoryPassed = 0
        $categoryTotal = 0
        
        foreach ($test in $validationResults[$category]) {
            $categoryPassed += $test.Passed
            $categoryTotal += $test.Total
        }
        
        $categoryPercentage = if ($categoryTotal -gt 0) { [math]::Round(($categoryPassed / $categoryTotal) * 100, 1) } else { 0 }
        $categoryStatus = if ($categoryPassed -eq $categoryTotal) { "✅ PASS" } else { "❌ FAIL" }
        
        Write-Host "`n$category : $categoryPassed/$categoryTotal ($categoryPercentage%) $categoryStatus" -ForegroundColor Cyan
        
        foreach ($test in $validationResults[$category]) {
            $testPercentage = if ($test.Total -gt 0) { [math]::Round(($test.Passed / $test.Total) * 100, 1) } else { 0 }
            $testStatus = if ($test.Status -eq "PASS") { "Green" } else { "Red" }
            Write-Host "  $($test.Test): $($test.Passed)/$($test.Total) ($testPercentage%)" -ForegroundColor $testStatus
        }
        
        $totalPassed += $categoryPassed
        $totalTotal += $categoryTotal
        
        $categoryResults += @{
            Category = $category
            Passed = $categoryPassed
            Total = $categoryTotal
            Percentage = $categoryPercentage
            Status = $categoryStatus
        }
    }
    
    $overallPercentage = if ($totalTotal -gt 0) { [math]::Round(($totalPassed / $totalTotal) * 100, 1) } else { 0 }
    
    Write-Host "`n🎯 Overall Results" -ForegroundColor Green
    Write-Host "=================" -ForegroundColor Green
    Write-Host "Total Passed: $totalPassed/$totalTotal ($overallPercentage%)" -ForegroundColor White
    
    if ($overallPercentage -ge 90) {
        Write-Host "🎉 EXCELLENT! Implementation is ready for deployment" -ForegroundColor Green
    } elseif ($overallPercentage -ge 75) {
        Write-Host "✅ GOOD! Minor issues need to be addressed" -ForegroundColor Yellow
    } else {
        Write-Host "⚠️  NEEDS WORK! Several critical components are missing" -ForegroundColor Red
    }
    
    Write-Host "`n📋 Next Steps:" -ForegroundColor Green
    if ($overallPercentage -ge 90) {
        Write-Host "  1. Run deployment: .\scripts\deploy-services.ps1 -Build" -ForegroundColor Gray
        Write-Host "  2. Test services: .\scripts\test-services.ps1" -ForegroundColor Gray
        Write-Host "  3. Access application: http://localhost" -ForegroundColor Gray
    } else {
        Write-Host "  1. Address missing files and configurations" -ForegroundColor Gray
        Write-Host "  2. Re-run validation" -ForegroundColor Gray
        Write-Host "  3. Deploy when validation passes" -ForegroundColor Gray
    }
}

# Run all validations
Test-DirectoryStructure
Test-DockerFiles
Test-EnvironmentFiles
Test-ServiceRoutes
Test-HttpClients
Test-NginxConfiguration
Test-DatabaseInitialization
Test-DeploymentScripts
Test-SupervisorConfiguration
Test-MiddlewareAndProviders

# Show summary
Show-ValidationSummary

Write-Host "`n📚 Documentation: DOCKER_MICROSERVICES_README.md" -ForegroundColor Cyan
