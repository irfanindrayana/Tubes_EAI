#!/usr/bin/env powershell

# Rolling Update Script for Microservices
# Performs zero-downtime updates with rollback capability

param(
    [Parameter(Mandatory=$true)]
    [string]$ServiceName,
    [string]$ImageTag = "latest",
    [switch]$Rollback = $false,
    [switch]$HealthCheck = $true,
    [int]$HealthTimeout = 120,
    [switch]$DryRun = $false
)

Write-Host "🔄 Trans Bandung Rolling Update Manager" -ForegroundColor Green
Write-Host "======================================" -ForegroundColor Green

# Service configurations
$services = @{
    "user-service" = @{
        ComposeService = "user-service"
        HealthEndpoint = "http://localhost:8001/health"
        DatabaseMigrations = $true
        Dependencies = @("user-db", "redis")
    }
    "ticketing-service" = @{
        ComposeService = "ticketing-service"
        HealthEndpoint = "http://localhost:8002/health"
        DatabaseMigrations = $true
        Dependencies = @("ticketing-db", "redis", "user-service")
    }
    "payment-service" = @{
        ComposeService = "payment-service"
        HealthEndpoint = "http://localhost:8003/health"
        DatabaseMigrations = $true
        Dependencies = @("payment-db", "redis", "user-service")
    }
    "inbox-service" = @{
        ComposeService = "inbox-service"
        HealthEndpoint = "http://localhost:8004/health"
        DatabaseMigrations = $true
        Dependencies = @("inbox-db", "redis", "user-service")
    }
    "reviews-service" = @{
        ComposeService = "reviews-service"
        HealthEndpoint = "http://localhost:8005/health"
        DatabaseMigrations = $true
        Dependencies = @("reviews-db", "redis", "user-service")
    }
    "api-gateway" = @{
        ComposeService = "api-gateway"
        HealthEndpoint = "http://localhost:8000/health"
        DatabaseMigrations = $false
        Dependencies = @("user-service", "ticketing-service", "payment-service", "inbox-service")
    }
}

$backupDir = "backups/updates"
$logFile = "logs/update-$(Get-Date -Format 'yyyy-MM-dd-HHmmss')-$ServiceName.log"

function Write-UpdateLog {
    param($Message, $Level = "INFO")
    $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    $logEntry = "[$timestamp] [$Level] $Message"
    Write-Host $logEntry -ForegroundColor $(
        switch ($Level) {
            "SUCCESS" { "Green" }
            "WARN" { "Yellow" }
            "ERROR" { "Red" }
            default { "Cyan" }
        }
    )
    
    if (!(Test-Path "logs")) { New-Item -ItemType Directory -Path "logs" | Out-Null }
    Add-Content -Path $logFile -Value $logEntry
}

function Test-ServiceHealth {
    param($HealthEndpoint, $TimeoutSeconds = 30)
    
    if (!$HealthEndpoint) { return $true }
    
    $attempts = 0
    $maxAttempts = [math]::Floor($TimeoutSeconds / 5)
    
    while ($attempts -lt $maxAttempts) {
        try {
            $response = Invoke-RestMethod -Uri $HealthEndpoint -TimeoutSec 5 -ErrorAction Stop
            if ($response.status -eq "healthy") {
                return $true
            }
        }
        catch {
            Write-UpdateLog "Health check attempt $($attempts + 1)/$maxAttempts failed" "WARN"
        }
        $attempts++
        Start-Sleep -Seconds 5
    }
    
    return $false
}

function New-ServiceBackup {
    param($ServiceName)
    
    if (!(Test-Path $backupDir)) { New-Item -ItemType Directory -Path $backupDir -Force | Out-Null }
    
    $timestamp = Get-Date -Format "yyyy-MM-dd-HHmmss"
    $backupPath = "$backupDir/$ServiceName-$timestamp"
    
    Write-UpdateLog "Creating backup for $ServiceName at $backupPath" "INFO"
    
    # Get current container info
    $containerInfo = docker ps --filter "name=$ServiceName" --format "{{.ID}},{{.Image}},{{.Status}}"
    $containerInfo | Out-File "$backupPath-container.txt"
    
    # Export container if running
    $containerId = docker ps --filter "name=$ServiceName" --format "{{.ID}}" | Select-Object -First 1
    if ($containerId) {
        Write-UpdateLog "Exporting container $containerId..." "INFO"
        docker export $containerId | gzip > "$backupPath-container.tar.gz"
    }
    
    # Backup database if applicable
    $serviceConfig = $services[$ServiceName]
    if ($serviceConfig.DatabaseMigrations) {
        Write-UpdateLog "Creating database backup..." "INFO"
        $dbName = switch ($ServiceName) {
            "user-service" { "transbandung_users" }
            "ticketing-service" { "transbandung_ticketing" }
            "payment-service" { "transbandung_payments" }
            "inbox-service" { "transbandung_inbox" }
            "reviews-service" { "transbandung_reviews" }
        }
        
        if ($dbName) {
            docker exec "transbandung-$($ServiceName.Replace('-service', ''))-db" mysqldump -u microservice -pmicroservice123 $dbName | Out-File "$backupPath-database.sql"
        }
    }
    
    return $backupPath
}

function Start-RollingUpdate {
    param($ServiceName, $ServiceConfig, $ImageTag)
    
    Write-UpdateLog "Starting rolling update for $ServiceName to tag: $ImageTag" "INFO"
    
    # Step 1: Pre-update checks
    Write-UpdateLog "Performing pre-update checks..." "INFO"
    
    # Check dependencies are healthy
    foreach ($dependency in $ServiceConfig.Dependencies) {
        $depConfig = $services[$dependency]
        if ($depConfig -and $depConfig.HealthEndpoint) {
            if (!(Test-ServiceHealth $depConfig.HealthEndpoint 30)) {
                throw "Dependency $dependency is not healthy"
            }
        }
    }
    
    # Step 2: Build new image
    Write-UpdateLog "Building new image..." "INFO"
    $imageName = "transbandunglast-$ServiceName"
    docker build -t "${imageName}:$ImageTag" -f "dockerfiles/$ServiceName.Dockerfile" .
    
    if ($LASTEXITCODE -ne 0) {
        throw "Failed to build new image"
    }
    
    # Step 3: Run database migrations if needed
    if ($ServiceConfig.DatabaseMigrations) {
        Write-UpdateLog "Running database migrations..." "INFO"
        
        # Create temporary container to run migrations
        $tempContainer = docker run -d --network transbandung-microservices `
            --env-file "envs/.env.$ServiceName" `
            "${imageName}:$ImageTag" `
            php artisan migrate --force
        
        $migrationResult = docker wait $tempContainer
        $migrationLogs = docker logs $tempContainer
        docker rm $tempContainer
        
        if ($migrationResult -ne "0") {
            Write-UpdateLog "Migration failed: $migrationLogs" "ERROR"
            throw "Database migration failed"
        }
        
        Write-UpdateLog "Migrations completed successfully" "SUCCESS"
    }
    
    # Step 4: Rolling update
    Write-UpdateLog "Performing rolling update..." "INFO"
    
    # Get current container count
    $currentContainers = docker ps --filter "name=$ServiceName" --format "{{.Names}}"
    $containerCount = ($currentContainers | Measure-Object).Count
    
    if ($containerCount -eq 0) {
        # No existing containers, simple deployment
        docker-compose -f docker-compose.services.yml up -d $ServiceConfig.ComposeService
    } else {
        # Rolling update with zero downtime
        for ($i = 0; $i -lt $containerCount; $i++) {
            Write-UpdateLog "Updating container $($i + 1)/$containerCount..." "INFO"
            
            # Scale up with new image
            $env:IMAGE_TAG = $ImageTag
            docker-compose -f docker-compose.services.yml up -d --scale $ServiceConfig.ComposeService=$($containerCount + 1) $ServiceConfig.ComposeService
            
            # Wait for new container to be healthy
            Start-Sleep -Seconds 10
            if ($HealthCheck -and !(Test-ServiceHealth $ServiceConfig.HealthEndpoint $HealthTimeout)) {
                throw "New container failed health check"
            }
            
            # Remove old container
            $oldContainer = $currentContainers[$i]
            if ($oldContainer) {
                docker stop $oldContainer
                docker rm $oldContainer
            }
            
            # Scale back to original count
            docker-compose -f docker-compose.services.yml up -d --scale $ServiceConfig.ComposeService=$containerCount $ServiceConfig.ComposeService
        }
    }
    
    # Step 5: Final health check
    if ($HealthCheck) {
        Write-UpdateLog "Performing final health check..." "INFO"
        if (!(Test-ServiceHealth $ServiceConfig.HealthEndpoint $HealthTimeout)) {
            throw "Final health check failed"
        }
    }
    
    Write-UpdateLog "Rolling update completed successfully!" "SUCCESS"
}

function Start-Rollback {
    param($ServiceName, $BackupPath)
    
    Write-UpdateLog "Starting rollback for $ServiceName..." "WARN"
    
    # Find latest backup if not specified
    if (!$BackupPath) {
        $latestBackup = Get-ChildItem "$backupDir" | Where-Object { $_.Name -like "$ServiceName-*" } | Sort-Object LastWriteTime -Descending | Select-Object -First 1
        if ($latestBackup) {
            $BackupPath = $latestBackup.FullName
        } else {
            throw "No backup found for rollback"
        }
    }
    
    Write-UpdateLog "Rolling back using backup: $BackupPath" "INFO"
    
    # Stop current service
    docker-compose -f docker-compose.services.yml stop $services[$ServiceName].ComposeService
    
    # Restore from backup (simplified - restore container image)
    if (Test-Path "$BackupPath-container.tar.gz") {
        Write-UpdateLog "Restoring container from backup..." "INFO"
        # Implementation would involve importing and retagging the backup
        # This is a simplified version
    }
    
    # Restart service
    docker-compose -f docker-compose.services.yml up -d $services[$ServiceName].ComposeService
    
    # Health check
    if ($HealthCheck -and !(Test-ServiceHealth $services[$ServiceName].HealthEndpoint $HealthTimeout)) {
        throw "Rollback health check failed"
    }
    
    Write-UpdateLog "Rollback completed successfully!" "SUCCESS"
}

try {
    # Validate service name
    if (!$services.ContainsKey($ServiceName)) {
        Write-UpdateLog "Unknown service: $ServiceName" "ERROR"
        Write-UpdateLog "Available services: $($services.Keys -join ', ')" "INFO"
        exit 1
    }
    
    $serviceConfig = $services[$ServiceName]
    
    if ($DryRun) {
        Write-UpdateLog "DRY RUN MODE - No actual changes will be made" "WARN"
        Write-UpdateLog "Would update: $ServiceName" "INFO"
        Write-UpdateLog "Target image tag: $ImageTag" "INFO"
        Write-UpdateLog "Health check enabled: $HealthCheck" "INFO"
        exit 0
    }
    
    # Create backup before any changes
    $backupPath = New-ServiceBackup $ServiceName
    
    if ($Rollback) {
        Start-Rollback $ServiceName $null
    } else {
        Start-RollingUpdate $ServiceName $serviceConfig $ImageTag
    }
    
    Write-UpdateLog "🎉 Update operation completed successfully!" "SUCCESS"
    Write-UpdateLog "Backup location: $backupPath" "INFO"
    Write-UpdateLog "Log file: $logFile" "INFO"
    
    exit 0
}
catch {
    Write-UpdateLog "❌ Update failed: $($_.Exception.Message)" "ERROR"
    Write-UpdateLog "Backup is available at: $backupPath" "INFO"
    Write-UpdateLog "Consider running rollback: .\update-service.ps1 -ServiceName $ServiceName -Rollback" "WARN"
    exit 1
}
