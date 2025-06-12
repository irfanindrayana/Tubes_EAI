# Trans Bandung Microservices Deployment Script
param(
    [switch]$Build = $false,
    [switch]$Down = $false,
    [switch]$Logs = $false
)

Write-Host "=== Trans Bandung Microservices Deployment ===" -ForegroundColor Green

$composeFile = "docker-compose.services.yml"

if (!(Test-Path $composeFile)) {
    Write-Host "ERROR: $composeFile not found!" -ForegroundColor Red
    exit 1
}

try {
    if ($Down) {
        Write-Host "Stopping all services..." -ForegroundColor Yellow
        docker-compose -f $composeFile down
        Write-Host "All services stopped." -ForegroundColor Green
        exit 0
    }

    if ($Logs) {
        Write-Host "Showing service logs..." -ForegroundColor Yellow
        docker-compose -f $composeFile logs -f
        exit 0
    }

    if ($Build) {
        Write-Host "Building Docker images..." -ForegroundColor Yellow
        docker-compose -f $composeFile build --no-cache
        if ($LASTEXITCODE -ne 0) {
            Write-Host "Build failed!" -ForegroundColor Red
            exit 1
        }
        Write-Host "Build completed successfully!" -ForegroundColor Green
    }

    Write-Host "Starting microservices..." -ForegroundColor Yellow
    docker-compose -f $composeFile up -d

    if ($LASTEXITCODE -eq 0) {
        Write-Host "Services started successfully!" -ForegroundColor Green
        
        Write-Host "`nChecking service status..." -ForegroundColor Yellow
        Start-Sleep -Seconds 5
        docker-compose -f $composeFile ps
        
        Write-Host "`nService URLs:" -ForegroundColor Cyan
        Write-Host "- API Gateway: http://localhost:8080" -ForegroundColor White
        Write-Host "- User Service: http://localhost:8081" -ForegroundColor White  
        Write-Host "- Ticketing Service: http://localhost:8082" -ForegroundColor White
        Write-Host "- Payment Service: http://localhost:8083" -ForegroundColor White
        Write-Host "- Inbox Service: http://localhost:8084" -ForegroundColor White
        
        Write-Host "`nTo check logs: .\scripts\deploy-simple.ps1 -Logs" -ForegroundColor Gray
        Write-Host "To stop services: .\scripts\deploy-simple.ps1 -Down" -ForegroundColor Gray
    } else {
        Write-Host "Failed to start services!" -ForegroundColor Red
        exit 1
    }

} catch {
    Write-Host "Deployment error: $($_.Exception.Message)" -ForegroundColor Red
    exit 1
}
