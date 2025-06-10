# Makefile for TransBandung Laravel Docker Environment

.PHONY: help build up down restart logs shell composer artisan test clean setup

# Default target
help: ## Show this help message
	@echo "TransBandung Laravel - Docker Commands"
	@echo "======================================"
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "\033[36m%-15s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)

# Setup and build
setup: ## Initial setup - copy env and build containers
	@echo "🚀 Setting up TransBandung Laravel..."
	@if [ ! -f .env ]; then cp .env.docker .env; echo "✅ Environment file created"; fi
	@$(MAKE) build
	@$(MAKE) up
	@sleep 10
	@$(MAKE) laravel-setup

build: ## Build Docker containers
	@echo "🔨 Building Docker containers..."
	docker-compose build --no-cache

# Container management
up: ## Start all containers
	@echo "▶️ Starting containers..."
	docker-compose up -d

down: ## Stop all containers
	@echo "⏹️ Stopping containers..."
	docker-compose down

restart: ## Restart all containers
	@echo "🔄 Restarting containers..."
	docker-compose restart

# Laravel specific
laravel-setup: ## Setup Laravel application (key, migrate, seed)
	@echo "🔧 Setting up Laravel application..."
	docker-compose exec app php artisan key:generate
	sleep 5
	docker-compose exec app php artisan microservices:create-databases
	docker-compose exec app php artisan migrate --force
	docker-compose exec app php artisan microservices:setup-databases
	docker-compose exec app php artisan db:seed --force
	docker-compose exec app php artisan config:clear
	docker-compose exec app php artisan cache:clear

artisan: ## Run artisan command (use: make artisan cmd="migrate")
	docker-compose exec app php artisan $(cmd)

composer: ## Run composer command (use: make composer cmd="install")
	docker-compose exec app composer $(cmd)

# Development
shell: ## Enter application container shell
	@echo "🐚 Entering application container..."
	docker-compose exec app bash

logs: ## Show container logs (use: make logs service="app")
	@if [ -z "$(service)" ]; then \
		docker-compose logs -f; \
	else \
		docker-compose logs -f $(service); \
	fi

# Frontend
npm: ## Run npm command (use: make npm cmd="install")
	docker-compose exec vite npm $(cmd)

build-assets: ## Build frontend assets
	docker-compose exec vite npm run build

dev-assets: ## Start Vite dev server
	docker-compose exec vite npm run dev

# Database
db-fresh: ## Fresh database migration and seed
	docker-compose exec app php artisan migrate:fresh --seed --force

db-backup: ## Backup all databases
	@echo "💾 Creating database backup..."
	@mkdir -p backups
	@timestamp=$$(date +%Y%m%d_%H%M%S); \
	for port in 3306 3307 3308 3309 3310 3311; do \
		db_name=$$(docker-compose exec mysql-users mysql -h mysql -P $$port -u root -proot123 -e "SELECT DATABASE();" 2>/dev/null | tail -1); \
		if [ ! -z "$$db_name" ] && [ "$$db_name" != "NULL" ]; then \
			docker-compose exec mysql mysqldump -h mysql -P $$port -u root -proot123 $$db_name > backups/$${db_name}_$${timestamp}.sql; \
			echo "✅ Backed up $$db_name"; \
		fi; \
	done

# Testing
test: ## Run PHPUnit tests
	docker-compose exec app php artisan test

test-feature: ## Run feature tests only
	docker-compose exec app php artisan test --testsuite=Feature

test-unit: ## Run unit tests only
	docker-compose exec app php artisan test --testsuite=Unit

# Maintenance
clean: ## Clean up containers, images, and volumes
	@echo "🧹 Cleaning up Docker resources..."
	docker-compose down -v --remove-orphans
	docker system prune -f
	docker volume prune -f

clean-all: ## Clean everything including images
	@echo "🧹 Cleaning all Docker resources..."
	docker-compose down -v --remove-orphans --rmi all
	docker system prune -af
	docker volume prune -f

reset: ## Reset everything and setup fresh
	@echo "🔄 Resetting everything..."
	$(MAKE) clean
	$(MAKE) setup

# Monitoring
status: ## Show container status
	@echo "📊 Container Status:"
	docker-compose ps

top: ## Show running processes in containers
	docker-compose top

# Production helpers
optimize: ## Optimize Laravel for production
	docker-compose exec app php artisan config:cache
	docker-compose exec app php artisan route:cache
	docker-compose exec app php artisan view:cache
	docker-compose exec app composer install --optimize-autoloader --no-dev

clear-cache: ## Clear all Laravel caches
	docker-compose exec app php artisan config:clear
	docker-compose exec app php artisan cache:clear
	docker-compose exec app php artisan route:clear
	docker-compose exec app php artisan view:clear

# Quick access URLs
urls: ## Show service URLs
	@echo "📋 Service URLs:"
	@echo "   - Laravel App: http://localhost:8000"
	@echo "   - phpMyAdmin: http://localhost:8080"  
	@echo "   - Vite Dev Server: http://localhost:5173"
	@echo ""
	@echo "🗄️  Database Connections:"
	@echo "   - Main MySQL: localhost:3306"
	@echo "   - Users DB: localhost:3307"
	@echo "   - Ticketing DB: localhost:3308"
	@echo "   - Payments DB: localhost:3309"
	@echo "   - Reviews DB: localhost:3310"
	@echo "   - Inbox DB: localhost:3311"
