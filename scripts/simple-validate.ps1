Write-Host "Microservices Validation Starting..." -ForegroundColor Green

# Check required files
$files = @(
    "docker-compose.services.yml",
    "dockerfiles/user-service.Dockerfile",
    "dockerfiles/payment-service.Dockerfile",
    "envs/.env.user-service"
)

$allGood = $true
foreach ($file in $files) {
    if (Test-Path $file) {
        Write-Host "[OK] $file exists" -ForegroundColor Green
    } else {
        Write-Host "[ERROR] $file missing" -ForegroundColor Red
        $allGood = $false
    }
}

if ($allGood) {
    Write-Host "Ready to deploy microservices!" -ForegroundColor Yellow
} else {
    Write-Host "Please fix missing files first" -ForegroundColor Red
}
