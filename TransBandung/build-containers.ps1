#!/usr/bin/env pwsh
# build-containers.ps1 - Script to build TransBandung containers with retry mechanisms

Write-Host "🚌 TransBandung Container Builder" -ForegroundColor Blue
Write-Host "=================================" -ForegroundColor Blue

# Max number of retries for docker commands
$MAX_RETRIES = 3
$RETRY_DELAY = 10 # seconds

function Retry-Command {
    param (
        [Parameter(Mandatory=$true)]
        [string]$Command,
        
        [Parameter(Mandatory=$true)]
        [string]$Description
    )
    
    $attempts = 0
    $success = $false
    
    while (-not $success -and $attempts -lt $MAX_RETRIES) {
        $attempts++
        
        try {
            Write-Host "[$attempts/$MAX_RETRIES] $Description..." -ForegroundColor Cyan
            
            # Execute the command
            Invoke-Expression $Command
            
            if ($LASTEXITCODE -eq 0) {
                $success = $true
                Write-Host "✅ Success!" -ForegroundColor Green
            } else {
                throw "Command exited with code $LASTEXITCODE"
            }
        } catch {
            $errorMessage = $_.Exception.Message
            Write-Host "❌ Attempt $attempts failed: $errorMessage" -ForegroundColor Red
            
            if ($attempts -lt $MAX_RETRIES) {
                Write-Host "Waiting $RETRY_DELAY seconds before retrying..." -ForegroundColor Yellow
                Start-Sleep -Seconds $RETRY_DELAY
            } else {
                Write-Host "Maximum retry attempts reached. Moving on..." -ForegroundColor Red
            }
        }
    }
    
    return $success
}

# Check if Docker is running
Write-Host "Checking if Docker is running..." -ForegroundColor Cyan
try {
    docker info > $null 2>&1
    if ($LASTEXITCODE -ne 0) {
        Write-Host "❌ Docker is not running. Please start Docker and try again." -ForegroundColor Red
        exit 1
    }
    Write-Host "✅ Docker is running." -ForegroundColor Green
} catch {
    Write-Host "❌ Docker is not available. Please install Docker and try again." -ForegroundColor Red
    exit 1
}

# Try to log in to Docker Hub (if credentials are available)
Write-Host "Checking Docker Hub authentication..." -ForegroundColor Cyan
docker info | Select-String "Username"
if ($LASTEXITCODE -ne 0) {
    Write-Host "⚠️ Not logged in to Docker Hub. Anonymous pulls may be rate-limited." -ForegroundColor Yellow
    $login = Read-Host -Prompt "Would you like to log in to Docker Hub? (y/n)"
    if ($login -eq "y") {
        docker login
    }
}

# Pre-pull required images to avoid issues during docker-compose
Write-Host "Pre-pulling required Docker images..." -ForegroundColor Cyan

$images = @(
    "nginx:1.25.1",
    "mysql:8.0",
    "node:16"
)

foreach ($image in $images) {
    Retry-Command -Command "docker pull $image" -Description "Pulling $image"
}

# Build and run the containers
Write-Host "Building and starting all services..." -ForegroundColor Cyan

# Force stop and remove existing containers first to ensure clean state
Write-Host "Stopping any existing containers..." -ForegroundColor Cyan
docker-compose down

# Build each service individually with retries
$services = @(
    "mysql",
    "user-service",
    "booking-service",
    "route-service",
    "review-service",
    "payment-service",
    "api-gateway",
    "frontend"
)

foreach ($service in $services) {
    Retry-Command -Command "docker-compose build $service" -Description "Building $service"
}

# Start all services
Retry-Command -Command "docker-compose up -d" -Description "Starting all services"

# Check if all containers are running
Write-Host "Verifying all containers are running..." -ForegroundColor Cyan
$containers = docker-compose ps -q
if ($containers) {
    docker ps --format "{{.Names}} - {{.Status}}" | Select-String "transbandung"
    Write-Host "✅ All services are up and running!" -ForegroundColor Green
    
    # Show access URLs
    Write-Host "`n📱 Access your application at:" -ForegroundColor Blue
    Write-Host "   - Frontend: http://localhost" -ForegroundColor Blue
    Write-Host "   - API Gateway: http://localhost:4000/graphql" -ForegroundColor Blue
} else {
    Write-Host "❌ Some services failed to start. Check the logs with 'docker-compose logs'" -ForegroundColor Red
}
