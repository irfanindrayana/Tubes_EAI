#!/usr/bin/env powershell

<#
.SYNOPSIS
    Database Migration Script for Trans Bandung Microservices
.DESCRIPTION
    Handles database migrations for individual microservices with proper isolation
.PARAMETER Service
    Target service for migration (user, ticketing, payment, inbox, reviews)
.PARAMETER Action
    Action to perform (migrate, rollback, seed, fresh, status)
.PARAMETER Step
    Number of steps for rollback
.PARAMETER Force
    Force migration without confirmation
#>

param(
    [Parameter(Mandatory=$false)]
    [ValidateSet("user", "ticketing", "payment", "inbox", "reviews", "all")]
    [string]$Service = "all",
    
    [Parameter(Mandatory=$false)]
    [ValidateSet("migrate", "rollback", "seed", "fresh", "status", "reset")]
    [string]$Action = "migrate",
    
    [Parameter(Mandatory=$false)]
    [int]$Step = 1,
    
    [Parameter(Mandatory=$false)]
    [switch]$Force,
    
    [Parameter(Mandatory=$false)]
    [switch]$Verbose
)

# Configuration
$composeFile = "docker-compose.services.yml"
$logFile = "storage/logs/migration-$(Get-Date -Format 'yyyy-MM-dd-HH-mm-ss').log"

# Service to container mapping
$serviceContainers = @{
    "user" = @{
        "container" = "transbandung-user-service"
        "database" = "transbandung_users"
        "db_container" = "transbandung-user-db"
    }
    "ticketing" = @{
        "container" = "transbandung-ticketing-service" 
        "database" = "transbandung_ticketing"
        "db_container" = "transbandung-ticketing-db"
    }
    "payment" = @{
        "container" = "transbandung-payment-service"
        "database" = "transbandung_payments" 
        "db_container" = "transbandung-payment-db"
    }
    "inbox" = @{
        "container" = "transbandung-inbox-service"
        "database" = "transbandung_inbox"
        "db_container" = "transbandung-inbox-db"
    }
    "reviews" = @{
        "container" = "transbandung-reviews-service"
        "database" = "transbandung_reviews"
        "db_container" = "transbandung-reviews-db"
    }
}

function Write-Log {
    param($Message, $Level = "INFO")
    $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    $logMessage = "[$timestamp] [$Level] $Message"
    
    Write-Host $logMessage
    Add-Content -Path $logFile -Value $logMessage
}

function Test-ServiceHealth {
    param($ServiceName)
    
    $container = $serviceContainers[$ServiceName].container
    
    try {
        $result = docker exec $container php artisan --version 2>&1
        if ($LASTEXITCODE -eq 0) {
            return $true
        }
    }
    catch {
        return $false
    }
    
    return $false
}

function Test-DatabaseConnection {
    param($ServiceName)
    
    $dbContainer = $serviceContainers[$ServiceName].db_container
    $database = $serviceContainers[$ServiceName].database
    
    try {
        $result = docker exec $dbContainer mysqladmin ping -h localhost 2>&1
        if ($LASTEXITCODE -eq 0) {
            Write-Log "Database connection successful for $ServiceName" "SUCCESS"
            return $true
        }
    }
    catch {
        Write-Log "Database connection failed for $ServiceName" "ERROR"
        return $false
    }
    
    return $false
}

function Invoke-Migration {
    param($ServiceName, $Action, $Step = 1)
    
    $container = $serviceContainers[$ServiceName].container
    $database = $serviceContainers[$ServiceName].database
    
    Write-Log "Starting $Action for $ServiceName service..." "INFO"
    
    # Check service health
    if (-not (Test-ServiceHealth $ServiceName)) {
        Write-Log "Service $ServiceName is not healthy. Skipping migration." "ERROR"
        return $false
    }
    
    # Check database connection
    if (-not (Test-DatabaseConnection $ServiceName)) {
        Write-Log "Database connection failed for $ServiceName. Skipping migration." "ERROR"
        return $false
    }
    
    try {
        switch ($Action) {
            "migrate" {
                Write-Log "Running migrations for $ServiceName..." "INFO"
                $result = docker exec $container php artisan migrate --force --database=mysql
                if ($LASTEXITCODE -eq 0) {
                    Write-Log "Migration successful for $ServiceName" "SUCCESS"
                } else {
                    Write-Log "Migration failed for $ServiceName" "ERROR"
                    return $false
                }
            }
            
            "rollback" {
                Write-Log "Rolling back $Step step(s) for $ServiceName..." "INFO"
                $result = docker exec $container php artisan migrate:rollback --step=$Step --force --database=mysql
                if ($LASTEXITCODE -eq 0) {
                    Write-Log "Rollback successful for $ServiceName" "SUCCESS"
                } else {
                    Write-Log "Rollback failed for $ServiceName" "ERROR"
                    return $false
                }
            }
            
            "fresh" {
                if (-not $Force) {
                    $confirm = Read-Host "This will drop all tables in $database. Continue? (y/N)"
                    if ($confirm -ne "y" -and $confirm -ne "Y") {
                        Write-Log "Fresh migration cancelled for $ServiceName" "INFO"
                        return $true
                    }
                }
                
                Write-Log "Running fresh migration for $ServiceName..." "INFO"
                $result = docker exec $container php artisan migrate:fresh --force --database=mysql
                if ($LASTEXITCODE -eq 0) {
                    Write-Log "Fresh migration successful for $ServiceName" "SUCCESS"
                } else {
                    Write-Log "Fresh migration failed for $ServiceName" "ERROR"
                    return $false
                }
            }
            
            "seed" {
                Write-Log "Running seeders for $ServiceName..." "INFO"
                $result = docker exec $container php artisan db:seed --force --database=mysql
                if ($LASTEXITCODE -eq 0) {
                    Write-Log "Seeding successful for $ServiceName" "SUCCESS"
                } else {
                    Write-Log "Seeding failed for $ServiceName" "ERROR"
                    return $false
                }
            }
            
            "status" {
                Write-Log "Checking migration status for $ServiceName..." "INFO"
                $result = docker exec $container php artisan migrate:status --database=mysql
                Write-Log "Migration status retrieved for $ServiceName" "INFO"
            }
            
            "reset" {
                if (-not $Force) {
                    $confirm = Read-Host "This will reset all migrations in $database. Continue? (y/N)"
                    if ($confirm -ne "y" -and $confirm -ne "Y") {
                        Write-Log "Reset cancelled for $ServiceName" "INFO"
                        return $true
                    }
                }
                
                Write-Log "Resetting migrations for $ServiceName..." "INFO"
                $result = docker exec $container php artisan migrate:reset --force --database=mysql
                if ($LASTEXITCODE -eq 0) {
                    Write-Log "Reset successful for $ServiceName" "SUCCESS"
                } else {
                    Write-Log "Reset failed for $ServiceName" "ERROR"
                    return $false
                }
            }
        }
        
        return $true
        
    }
    catch {
        Write-Log "Migration operation failed for $ServiceName`: $($_.Exception.Message)" "ERROR"
        return $false
    }
}

function Show-MigrationStatus {
    Write-Host ""
    Write-Host "🔍 Migration Status Overview" -ForegroundColor Cyan
    Write-Host "=" * 60
    
    foreach ($serviceName in $serviceContainers.Keys) {
        $container = $serviceContainers[$serviceName].container
        $database = $serviceContainers[$serviceName].database
        
        Write-Host ""
        Write-Host "Service: $($serviceName.ToUpper())" -ForegroundColor Yellow
        Write-Host "Database: $database" -ForegroundColor Gray
        
        if (Test-ServiceHealth $serviceName) {
            Write-Host "Service Status: ✅ Healthy" -ForegroundColor Green
            
            if (Test-DatabaseConnection $serviceName) {
                Write-Host "Database Status: ✅ Connected" -ForegroundColor Green
                
                # Get migration status
                try {
                    $migrationStatus = docker exec $container php artisan migrate:status --database=mysql 2>&1
                    if ($LASTEXITCODE -eq 0) {
                        Write-Host "Migrations:" -ForegroundColor Cyan
                        Write-Host $migrationStatus -ForegroundColor White
                    } else {
                        Write-Host "❌ Failed to get migration status" -ForegroundColor Red
                    }
                }
                catch {
                    Write-Host "❌ Error checking migrations" -ForegroundColor Red
                }
            } else {
                Write-Host "Database Status: ❌ Connection Failed" -ForegroundColor Red
            }
        } else {
            Write-Host "Service Status: ❌ Unhealthy" -ForegroundColor Red
        }
        
        Write-Host "-" * 40
    }
}

function Backup-Database {
    param($ServiceName)
    
    $dbContainer = $serviceContainers[$ServiceName].db_container
    $database = $serviceContainers[$ServiceName].database
    $backupFile = "backups/db-backup-$ServiceName-$(Get-Date -Format 'yyyy-MM-dd-HH-mm-ss').sql"
    
    # Create backup directory
    if (-not (Test-Path "backups")) {
        New-Item -ItemType Directory -Path "backups" | Out-Null
    }
    
    Write-Log "Creating backup for $ServiceName database..." "INFO"
    
    try {
        $result = docker exec $dbContainer mysqldump -u root -proot123 $database > $backupFile
        if ($LASTEXITCODE -eq 0) {
            Write-Log "Backup created: $backupFile" "SUCCESS"
            return $backupFile
        } else {
            Write-Log "Backup failed for $ServiceName" "ERROR"
            return $null
        }
    }
    catch {
        Write-Log "Backup error for $ServiceName`: $($_.Exception.Message)" "ERROR"
        return $null
    }
}

# Main execution
Write-Host ""
Write-Host "🚀 Trans Bandung Microservices Database Migration" -ForegroundColor Cyan
Write-Host "=" * 60

# Create log directory
$logDir = Split-Path $logFile -Parent
if (-not (Test-Path $logDir)) {
    New-Item -ItemType Directory -Path $logDir -Force | Out-Null
}

Write-Log "Migration script started" "INFO"
Write-Log "Service: $Service, Action: $Action" "INFO"

# Validate Docker Compose
if (-not (Test-Path $composeFile)) {
    Write-Log "Docker Compose file not found: $composeFile" "ERROR"
    exit 1
}

# Check if services are running
Write-Log "Checking service status..." "INFO"
$runningServices = docker-compose -f $composeFile ps --services --filter status=running

if ($Action -eq "status") {
    Show-MigrationStatus
    exit 0
}

# Handle specific service or all services
$servicesToProcess = @()

if ($Service -eq "all") {
    $servicesToProcess = $serviceContainers.Keys
} else {
    if ($serviceContainers.ContainsKey($Service)) {
        $servicesToProcess = @($Service)
    } else {
        Write-Log "Invalid service name: $Service" "ERROR"
        exit 1
    }
}

$successCount = 0
$totalCount = $servicesToProcess.Count

foreach ($serviceName in $servicesToProcess) {
    Write-Host ""
    Write-Host "Processing service: $($serviceName.ToUpper())" -ForegroundColor Yellow
    
    # Create backup for destructive operations
    if ($Action -in @("fresh", "reset") -and -not $Force) {
        $backupFile = Backup-Database $serviceName
        if ($backupFile) {
            Write-Log "Backup created before $Action operation" "INFO"
        }
    }
    
    if (Invoke-Migration $serviceName $Action $Step) {
        $successCount++
        Write-Host "✅ $serviceName completed successfully" -ForegroundColor Green
    } else {
        Write-Host "❌ $serviceName failed" -ForegroundColor Red
    }
}

Write-Host ""
Write-Host "🏁 Migration Summary" -ForegroundColor Cyan
Write-Host "=" * 40
Write-Host "Total Services: $totalCount"
Write-Host "Successful: $successCount" -ForegroundColor Green
Write-Host "Failed: $($totalCount - $successCount)" -ForegroundColor Red
Write-Host ""
Write-Host "Log file: $logFile"

Write-Log "Migration script completed" "INFO"
Write-Log "Success rate: $successCount/$totalCount" "INFO"

if ($successCount -eq $totalCount) {
    Write-Host "🎉 All migrations completed successfully!" -ForegroundColor Green
    exit 0
} else {
    Write-Host "⚠️  Some migrations failed. Check the log file for details." -ForegroundColor Yellow
    exit 1
}
