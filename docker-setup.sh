#!/bin/bash

# Docker setup script for TransBandung Laravel Application

echo "🚀 Setting up TransBandung Laravel with Docker..."

# Function to check if Docker is running
check_docker() {
    if ! docker info > /dev/null 2>&1; then
        echo "❌ Docker is not running. Please start Docker and try again."
        exit 1
    fi
    echo "✅ Docker is running"
}

# Function to copy environment file
setup_env() {
    if [ ! -f .env ]; then
        echo "📋 Copying Docker environment file..."
        cp .env.docker .env
        echo "✅ Environment file created"
    else
        echo "⚠️  .env file already exists. Please manually update it with Docker settings if needed."
    fi
}

# Function to build and start containers
start_containers() {
    echo "🔨 Building and starting Docker containers..."
    docker-compose down --remove-orphans
    docker-compose build --no-cache
    docker-compose up -d
    
    echo "⏳ Waiting for containers to start..."
    sleep 10
}

# Function to setup Laravel application
setup_laravel() {
    echo "🔧 Setting up Laravel application..."
    
    # Generate application key
    docker-compose exec app php artisan key:generate
    
    # Wait for databases to be ready
    echo "⏳ Waiting for databases to be ready..."
    sleep 15
    
    # Create microservice databases
    echo "🗄️  Creating microservice databases..."
    docker-compose exec app php artisan microservices:create-databases
    
    # Run migrations
    echo "📊 Running database migrations..."
    docker-compose exec app php artisan migrate --force
    
    # Setup microservice databases
    echo "🏗️  Setting up microservice databases..."
    docker-compose exec app php artisan microservices:setup-databases
    
    # Seed database
    echo "🌱 Seeding database..."
    docker-compose exec app php artisan db:seed --force
    
    # Clear and cache configurations
    echo "🧹 Clearing and caching configurations..."
    docker-compose exec app php artisan config:clear
    docker-compose exec app php artisan cache:clear
    docker-compose exec app php artisan route:clear
    docker-compose exec app php artisan view:clear
    
    # Set proper permissions
    echo "🔒 Setting proper permissions..."
    docker-compose exec app chown -R www:www /var/www/html/storage
    docker-compose exec app chown -R www:www /var/www/html/bootstrap/cache
}

# Function to show status
show_status() {
    echo ""
    echo "🎉 Setup completed successfully!"
    echo ""
    echo "📋 Service URLs:"
    echo "   - Laravel App: http://localhost:8000"
    echo "   - phpMyAdmin: http://localhost:8080"
    echo "   - Vite Dev Server: http://localhost:5173"
    echo ""
    echo "🗄️  Database Connections:"
    echo "   - Main MySQL: localhost:3306"
    echo "   - Users DB: localhost:3307"
    echo "   - Ticketing DB: localhost:3308"
    echo "   - Payments DB: localhost:3309"
    echo "   - Reviews DB: localhost:3310"
    echo "   - Inbox DB: localhost:3311"
    echo "   - Redis: localhost:6379"
    echo ""
    echo "🔧 Useful commands:"
    echo "   - View logs: docker-compose logs -f"
    echo "   - Enter app container: docker-compose exec app bash"
    echo "   - Run artisan commands: docker-compose exec app php artisan [command]"
    echo "   - Stop containers: docker-compose down"
    echo "   - Restart containers: docker-compose restart"
    echo ""
}

# Main execution
main() {
    check_docker
    setup_env
    start_containers
    setup_laravel
    show_status
}

# Run main function
main
