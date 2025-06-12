# Quick Validation Script for Microservices
Write-Host "🔍 Quick Validation of Microservices Implementation" -ForegroundColor Green

# Check required files exist
$requiredFiles = @(
    "docker-compose.services.yml",
    "dockerfiles/user-service.Dockerfile",
    "dockerfiles/ticketing-service.Dockerfile", 
    "dockerfiles/payment-service.Dockerfile",
    "dockerfiles/inbox-service.Dockerfile",
    "dockerfiles/api-gateway.Dockerfile",
    "envs/.env.user-service",
    "envs/.env.payment-service",
    "envs/.env.inbox-service",
    "envs/.env.api-gateway"
)

$missing = @()
foreach ($file in $requiredFiles) {
    if (!(Test-Path $file)) {
        $missing += $file
    }
}

if ($missing.Count -eq 0) {
    Write-Host "[OK] All required files present" -ForegroundColor Green
    Write-Host "Ready for deployment!" -ForegroundColor Yellow
} else {
    Write-Host "[ERROR] Missing files:" -ForegroundColor Red
    $missing | ForEach-Object { Write-Host "  - $_" -ForegroundColor Red }
}

# Check Docker
try {
    docker --version | Out-Null
    Write-Host "[OK] Docker available" -ForegroundColor Green
} catch {
    Write-Host "[ERROR] Docker not available" -ForegroundColor Red
}

# Check Docker Compose
try {
    docker-compose --version | Out-Null
    Write-Host "[OK] Docker Compose available" -ForegroundColor Green
} catch {
    Write-Host "[ERROR] Docker Compose not available" -ForegroundColor Red
}
