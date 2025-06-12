#!/usr/bin/env powershell

<#
.SYNOPSIS
    Master Deployment Script for Trans Bandung Microservices
.DESCRIPTION
    Complete deployment automation with all enterprise features
.PARAMETER Environment
    Target environment (development, staging, production)
.PARAMETER Profile
    Performance profile (development, production, high-performance)
.PARAMETER SkipBuild
    Skip Docker image building
.PARAMETER Quick
    Quick deployment without optimization steps
.PARAMETER Clean
    Clean deployment (removes existing data)
#>

param(
    [Parameter(Mandatory=$false)]
    [ValidateSet("development", "staging", "production")]
    [string]$Environment = "production",
    
    [Parameter(Mandatory=$false)]
    [ValidateSet("development", "production", "high-performance")]
    [string]$Profile = "production",
    
    [Parameter(Mandatory=$false)]
    [switch]$SkipBuild,
    
    [Parameter(Mandatory=$false)]
    [switch]$Quick,
    
    [Parameter(Mandatory=$false)]
    [switch]$Clean,
    
    [Parameter(Mandatory=$false)]
    [switch]$Monitor,
    
    [Parameter(Mandatory=$false)]
    [switch]$Verbose
)

# Configuration
$logFile = "storage/logs/master-deployment-$(Get-Date -Format 'yyyy-MM-dd-HH-mm-ss').log"
$startTime = Get-Date

function Write-Log {
    param($Message, $Level = "INFO")
    $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    $logMessage = "[$timestamp] [$Level] $Message"
    
    Write-Host $logMessage
    if (Test-Path $logFile) {
        Add-Content -Path $logFile -Value $logMessage
    }
}

function Write-Banner {
    param($Title)
    Write-Host ""
    Write-Host "=" * 60 -ForegroundColor Cyan
    Write-Host " $Title" -ForegroundColor Cyan
    Write-Host "=" * 60 -ForegroundColor Cyan
    Write-Host ""
}

function Test-Prerequisites {
    Write-Log "Checking deployment prerequisites..." "INFO"
    
    $prerequisites = @{
        "Docker" = { docker --version }
        "Docker Compose" = { docker-compose --version }
        "PowerShell" = { $PSVersionTable.PSVersion.Major -ge 5 }
    }
    
    $failed = @()
    
    foreach ($prereq in $prerequisites.Keys) {
        try {
            $result = & $prerequisites[$prereq]
            if ($result) {
                Write-Log "✅ $prereq is available" "SUCCESS"
            } else {
                Write-Log "❌ $prereq check failed" "ERROR"
                $failed += $prereq
            }
        }
        catch {
            Write-Log "❌ $prereq is not available" "ERROR"
            $failed += $prereq
        }
    }
    
    if ($failed.Count -gt 0) {
        Write-Log "Missing prerequisites: $($failed -join ', ')" "ERROR"
        return $false
    }
    
    return $true
}

function Initialize-Environment {
    Write-Log "Initializing deployment environment..." "INFO"
    
    # Create necessary directories
    $directories = @(
        "storage/logs",
        "backups",
        "monitoring/reports",
        "security/logs"
    )
    
    foreach ($dir in $directories) {
        if (-not (Test-Path $dir)) {
            New-Item -ItemType Directory -Path $dir -Force | Out-Null
            Write-Log "Created directory: $dir" "INFO"
        }
    }
    
    # Initialize log file
    New-Item -ItemType File -Path $logFile -Force | Out-Null
    Write-Log "Master deployment started" "INFO"
    Write-Log "Environment: $Environment, Profile: $Profile" "INFO"
}

function Deploy-Services {
    Write-Banner "🚀 DEPLOYING MICROSERVICES"
    
    $deployArgs = @()
    if (-not $SkipBuild) { $deployArgs += "-Build" }
    if ($Clean) { $deployArgs += "-Clean" }
    if ($Verbose) { $deployArgs += "-Verbose" }
    
    Write-Log "Executing service deployment..." "INFO"
    & ".\scripts\deploy-services.ps1" @deployArgs
    
    if ($LASTEXITCODE -ne 0) {
        Write-Log "Service deployment failed" "ERROR"
        return $false
    }
    
    Write-Log "Service deployment completed successfully" "SUCCESS"
    return $true
}

function Apply-SecurityHardening {
    if ($Quick) {
        Write-Log "Skipping security hardening (quick mode)" "INFO"
        return $true
    }
    
    Write-Banner "🔒 APPLYING SECURITY HARDENING"
    
    Write-Log "Applying security hardening..." "INFO"
    & ".\scripts\security-hardening.ps1" -Action all -Domain localhost
    
    if ($LASTEXITCODE -ne 0) {
        Write-Log "Security hardening failed" "WARNING"
        return $false
    }
    
    Write-Log "Security hardening completed" "SUCCESS"
    return $true
}

function Optimize-Performance {
    if ($Quick) {
        Write-Log "Skipping performance optimization (quick mode)" "INFO"
        return $true
    }
    
    Write-Banner "⚡ OPTIMIZING PERFORMANCE"
    
    Write-Log "Applying performance optimizations..." "INFO"
    & ".\scripts\performance-optimization.ps1" -Target all -Profile $Profile
    
    if ($LASTEXITCODE -ne 0) {
        Write-Log "Performance optimization failed" "WARNING"
        return $false
    }
    
    Write-Log "Performance optimization completed" "SUCCESS"
    return $true
}

function Run-DatabaseMigrations {
    Write-Banner "🗃️  RUNNING DATABASE MIGRATIONS"
    
    Write-Log "Running database migrations..." "INFO"
    & ".\scripts\migrate-databases.ps1" -Service all -Action migrate
    
    if ($LASTEXITCODE -ne 0) {
        Write-Log "Database migrations failed" "ERROR"
        return $false
    }
    
    Write-Log "Database migrations completed" "SUCCESS"
    return $true
}

function Validate-Deployment {
    Write-Banner "🔍 VALIDATING DEPLOYMENT"
    
    Write-Log "Validating deployment..." "INFO"
    & ".\scripts\validate-deployment.ps1"
    
    if ($LASTEXITCODE -ne 0) {
        Write-Log "Deployment validation failed" "ERROR"
        return $false
    }
    
    Write-Log "Deployment validation passed" "SUCCESS"
    return $true
}

function Start-Monitoring {
    if (-not $Monitor) {
        Write-Log "Monitoring not requested" "INFO"
        return $true
    }
    
    Write-Banner "📊 STARTING MONITORING"
    
    Write-Log "Starting monitoring services..." "INFO"
    
    # Start health monitoring
    Start-Process -FilePath "powershell" -ArgumentList @(
        "-File", ".\scripts\health-check.ps1",
        "-Service", "all",
        "-Continuous",
        "-Interval", "30"
    ) -WindowStyle Minimized
    
    Write-Log "Monitoring services started" "SUCCESS"
    return $true
}

function Generate-DeploymentReport {
    Write-Banner "📋 GENERATING DEPLOYMENT REPORT"
    
    $endTime = Get-Date
    $duration = $endTime - $startTime
    
    $report = @"
# Trans Bandung Microservices Deployment Report

## Deployment Summary
- **Start Time**: $($startTime.ToString("yyyy-MM-dd HH:mm:ss"))
- **End Time**: $($endTime.ToString("yyyy-MM-dd HH:mm:ss"))
- **Duration**: $($duration.ToString("hh\:mm\:ss"))
- **Environment**: $Environment
- **Profile**: $Profile
- **Status**: SUCCESS

## Deployment Steps Completed
✅ Prerequisites Check
✅ Environment Initialization
✅ Service Deployment
$(if (-not $Quick) { "✅ Security Hardening" } else { "⏭️  Security Hardening (Skipped)" })
$(if (-not $Quick) { "✅ Performance Optimization" } else { "⏭️  Performance Optimization (Skipped)" })
✅ Database Migrations
✅ Deployment Validation
$(if ($Monitor) { "✅ Monitoring Setup" } else { "⏭️  Monitoring (Not Requested)" })

## Service Endpoints
- **Load Balancer**: http://localhost
- **API Gateway**: http://localhost:8000
- **User Service**: http://localhost:8001
- **Ticketing Service**: http://localhost:8002
- **Payment Service**: http://localhost:8003
- **Inbox Service**: http://localhost:8004
- **Reviews Service**: http://localhost:8005
- **phpMyAdmin**: http://localhost:8080

## Management Commands
```powershell
# View service status
.\scripts\health-check.ps1 -Service all

# View logs
.\scripts\logs-service.ps1 -Service api-gateway -Follow

# Scale services
.\scripts\scale-service.ps1 -Service user-service -Replicas 3

# Monitor performance
.\optimization\monitoring\dashboard.ps1

# View monitoring dashboard
Start-Process "resources\views\monitoring\dashboard.html"
```

## Configuration Files
- **Services**: docker-compose.services.yml
- **Security**: security/
- **Performance**: optimization/
- **Logs**: storage/logs/
- **Monitoring**: resources/views/monitoring/

## Next Steps
1. Monitor service health and performance
2. Review security configurations
3. Set up automated backups
4. Configure alerts and monitoring
5. Plan scaling and updates

---
Generated on: $($endTime.ToString("yyyy-MM-dd HH:mm:ss"))
"@
    
    $reportFile = "deployment-report-$(Get-Date -Format 'yyyy-MM-dd-HH-mm-ss').md"
    Set-Content -Path $reportFile -Value $report
    
    Write-Log "Deployment report generated: $reportFile" "SUCCESS"
    
    # Display summary
    Write-Host ""
    Write-Host "🎉 DEPLOYMENT COMPLETED SUCCESSFULLY!" -ForegroundColor Green
    Write-Host ""
    Write-Host "📊 Summary:" -ForegroundColor Cyan
    Write-Host "  Duration: $($duration.ToString("hh\:mm\:ss"))"
    Write-Host "  Environment: $Environment"
    Write-Host "  Profile: $Profile"
    Write-Host "  Report: $reportFile"
    Write-Host ""
    Write-Host "🌐 Access Points:" -ForegroundColor Yellow
    Write-Host "  Main Application: http://localhost"
    Write-Host "  API Gateway: http://localhost:8000"
    Write-Host "  Monitoring Dashboard: resources/views/monitoring/dashboard.html"
    Write-Host ""
    Write-Host "📚 Documentation: MICROSERVICES_COMPLETE_GUIDE.md" -ForegroundColor Cyan
}

function Handle-DeploymentFailure {
    param($FailedStep)
    
    Write-Host ""
    Write-Host "❌ DEPLOYMENT FAILED" -ForegroundColor Red
    Write-Host "Failed at step: $FailedStep" -ForegroundColor Red
    Write-Host ""
    Write-Host "🔧 Troubleshooting:" -ForegroundColor Yellow
    Write-Host "1. Check the log file: $logFile"
    Write-Host "2. Verify Docker is running: docker ps"
    Write-Host "3. Check port availability: netstat -an | findstr '8000'"
    Write-Host "4. Review service logs: .\scripts\logs-service.ps1 -Service all -Errors"
    Write-Host "5. Restart failed services: docker-compose -f docker-compose.services.yml restart"
    Write-Host ""
    Write-Host "📚 For detailed troubleshooting, see: MICROSERVICES_COMPLETE_GUIDE.md"
    
    exit 1
}

# Main execution
Clear-Host
Write-Host ""
Write-Host "🐳 Trans Bandung Microservices - Master Deployment" -ForegroundColor Cyan
Write-Host "================================================================"
Write-Host "Environment: $Environment | Profile: $Profile | Mode: $(if ($Quick) { 'Quick' } else { 'Complete' })"
Write-Host "================================================================"

try {
    # Initialize
    if (-not (Test-Prerequisites)) {
        Handle-DeploymentFailure "Prerequisites Check"
    }
    
    Initialize-Environment
    
    # Core deployment steps
    if (-not (Deploy-Services)) {
        Handle-DeploymentFailure "Service Deployment"
    }
    
    if (-not (Apply-SecurityHardening)) {
        Handle-DeploymentFailure "Security Hardening"
    }
    
    if (-not (Optimize-Performance)) {
        Handle-DeploymentFailure "Performance Optimization"
    }
    
    if (-not (Run-DatabaseMigrations)) {
        Handle-DeploymentFailure "Database Migrations"
    }
    
    if (-not (Validate-Deployment)) {
        Handle-DeploymentFailure "Deployment Validation"
    }
    
    if (-not (Start-Monitoring)) {
        Handle-DeploymentFailure "Monitoring Setup"
    }
    
    # Generate final report
    Generate-DeploymentReport
    
}
catch {
    Write-Log "Unexpected error during deployment: $($_.Exception.Message)" "ERROR"
    Handle-DeploymentFailure "Unexpected Error"
}
