# Docker setup script for TransBandung Laravel Application (Windows)

Write-Host "🚀 Setting up TransBandung Laravel with Docker..." -ForegroundColor Green

# Function to check if Docker is running
function Test-Docker {
    try {
        docker info | Out-Null
        Write-Host "✅ Docker is running" -ForegroundColor Green
        return $true
    }
    catch {
        Write-Host "❌ Docker is not running. Please start Docker and try again." -ForegroundColor Red
        return $false
    }
}

# Function to copy environment file
function Set-Environment {
    if (-not (Test-Path ".env")) {
        Write-Host "📋 Copying Docker environment file..." -ForegroundColor Yellow
        Copy-Item ".env.docker" ".env"
        Write-Host "✅ Environment file created" -ForegroundColor Green
    }
    else {
        Write-Host "⚠️  .env file already exists. Please manually update it with Docker settings if needed." -ForegroundColor Yellow
    }
}

# Function to build and start containers
function Start-Containers {
    Write-Host "🔨 Building and starting Docker containers..." -ForegroundColor Yellow
    
    docker-compose down --remove-orphans
    docker-compose build --no-cache
    docker-compose up -d
    
    Write-Host "⏳ Waiting for containers to start..." -ForegroundColor Yellow
    Start-Sleep -Seconds 10
}

# Function to setup Laravel application
function Set-Laravel {
    Write-Host "🔧 Setting up Laravel application..." -ForegroundColor Yellow
    
    # Generate application key
    docker-compose exec app php artisan key:generate
    
    # Wait for databases to be ready
    Write-Host "⏳ Waiting for databases to be ready..." -ForegroundColor Yellow
    Start-Sleep -Seconds 15
    
    # Create microservice databases
    Write-Host "🗄️  Creating microservice databases..." -ForegroundColor Yellow
    docker-compose exec app php artisan microservices:create-databases
    
    # Run migrations
    Write-Host "📊 Running database migrations..." -ForegroundColor Yellow
    docker-compose exec app php artisan migrate --force
    
    # Setup microservice databases
    Write-Host "🏗️  Setting up microservice databases..." -ForegroundColor Yellow
    docker-compose exec app php artisan microservices:setup-databases
    
    # Seed database
    Write-Host "🌱 Seeding database..." -ForegroundColor Yellow
    docker-compose exec app php artisan db:seed --force
    
    # Clear and cache configurations
    Write-Host "🧹 Clearing and caching configurations..." -ForegroundColor Yellow
    docker-compose exec app php artisan config:clear
    docker-compose exec app php artisan cache:clear
    docker-compose exec app php artisan route:clear
    docker-compose exec app php artisan view:clear
    
    # Set proper permissions
    Write-Host "🔒 Setting proper permissions..." -ForegroundColor Yellow
    docker-compose exec app chown -R www:www /var/www/html/storage
    docker-compose exec app chown -R www:www /var/www/html/bootstrap/cache
}

# Function to show status
function Show-Status {
    Write-Host ""
    Write-Host "🎉 Setup completed successfully!" -ForegroundColor Green
    Write-Host ""
    Write-Host "📋 Service URLs:" -ForegroundColor Cyan
    Write-Host "   - Laravel App: http://localhost:8000" -ForegroundColor White
    Write-Host "   - phpMyAdmin: http://localhost:8080" -ForegroundColor White
    Write-Host "   - Vite Dev Server: http://localhost:5173" -ForegroundColor White
    Write-Host ""
    Write-Host "🗄️  Database Connections:" -ForegroundColor Cyan
    Write-Host "   - Main MySQL: localhost:3306" -ForegroundColor White
    Write-Host "   - Users DB: localhost:3307" -ForegroundColor White
    Write-Host "   - Ticketing DB: localhost:3308" -ForegroundColor White
    Write-Host "   - Payments DB: localhost:3309" -ForegroundColor White
    Write-Host "   - Reviews DB: localhost:3310" -ForegroundColor White
    Write-Host "   - Inbox DB: localhost:3311" -ForegroundColor White
    Write-Host "   - Redis: localhost:6379" -ForegroundColor White
    Write-Host ""
    Write-Host "🔧 Useful commands:" -ForegroundColor Cyan
    Write-Host "   - View logs: docker-compose logs -f" -ForegroundColor White
    Write-Host "   - Enter app container: docker-compose exec app bash" -ForegroundColor White
    Write-Host "   - Run artisan commands: docker-compose exec app php artisan [command]" -ForegroundColor White
    Write-Host "   - Stop containers: docker-compose down" -ForegroundColor White
    Write-Host "   - Restart containers: docker-compose restart" -ForegroundColor White
    Write-Host ""
}

# Main execution
if (Test-Docker) {
    Set-Environment
    Start-Containers
    Set-Laravel
    Show-Status
}
else {
    exit 1
}
