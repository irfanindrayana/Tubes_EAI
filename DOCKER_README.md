# TransBandung Laravel - Docker Development Setup

## Prerequisites

- Docker Desktop (Windows/Mac) atau Docker Engine (Linux)
- Docker Compose
- Git

## Quick Start

### Windows (PowerShell)
```powershell
# Jalankan script setup otomatis
.\docker-setup.ps1
```

### Linux/Mac (Bash)
```bash
# Berikan permission untuk script
chmod +x docker-setup.sh

# Jalankan script setup otomatis
./docker-setup.sh
```

### Manual Setup

1. **Copy Environment File**
   ```bash
   cp .env.docker .env
   ```

2. **Build dan Start Containers**
   ```bash
   docker-compose up -d --build
   ```

3. **Setup Laravel Application**
   ```bash
   # Generate application key
   docker-compose exec app php artisan key:generate
   
   # Create microservice databases
   docker-compose exec app php artisan microservices:create-databases
   
   # Run migrations
   docker-compose exec app php artisan migrate --force
   
   # Setup microservice databases
   docker-compose exec app php artisan microservices:setup-databases
   
   # Seed database
   docker-compose exec app php artisan db:seed --force
   ```

## Services

### Laravel Application
- **URL**: http://localhost:8000
- **Container**: `transbandung-app`
- **Description**: Main Laravel application dengan Nginx + PHP-FPM

### Database Services (MySQL 8.0)
- **Main Database**: localhost:3306 (container: `transbandung-mysql`)
- **Users Service**: localhost:3307 (container: `transbandung-mysql-users`)
- **Ticketing Service**: localhost:3308 (container: `transbandung-mysql-ticketing`)
- **Payment Service**: localhost:3309 (container: `transbandung-mysql-payments`)
- **Reviews Service**: localhost:3310 (container: `transbandung-mysql-reviews`)
- **Inbox Service**: localhost:3311 (container: `transbandung-mysql-inbox`)

### Caching & Queue
- **Redis**: localhost:6379 (container: `transbandung-redis`)
- **Queue Worker**: Background service untuk menjalankan jobs
- **Scheduler**: Background service untuk cron jobs

### Development Tools
- **phpMyAdmin**: http://localhost:8080 (container: `transbandung-phpmyadmin`)
- **Vite Dev Server**: http://localhost:5173 (container: `transbandung-vite`)

## Database Credentials

```
Username: root
Password: root123
```

## Common Commands

### Container Management
```bash
# Start all services
docker-compose up -d

# Stop all services
docker-compose down

# Restart services
docker-compose restart

# View logs
docker-compose logs -f [service-name]

# Enter application container
docker-compose exec app bash
```

### Laravel Commands
```bash
# Run Artisan commands
docker-compose exec app php artisan [command]

# Install Composer dependencies
docker-compose exec app composer install

# Install NPM dependencies
docker-compose exec vite npm install

# Build frontend assets
docker-compose exec vite npm run build

# Run migrations
docker-compose exec app php artisan migrate

# Clear caches
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan route:clear
docker-compose exec app php artisan view:clear
```

### Database Operations
```bash
# Create microservice databases
docker-compose exec app php artisan microservices:create-databases

# Setup microservice databases with migrations
docker-compose exec app php artisan microservices:setup-databases

# Test database connections
docker-compose exec app php artisan microservices:test

# Seed databases
docker-compose exec app php artisan db:seed --force
```

## File Structure

```
docker/
├── nginx/
│   └── default.conf          # Nginx virtual host configuration
├── php/
│   ├── php.ini              # PHP configuration
│   ├── php-fpm.conf         # PHP-FPM configuration
│   └── local.ini            # Additional PHP settings
├── mysql/
│   ├── my.cnf               # MySQL configuration
│   └── init.sql             # Database initialization script
├── supervisor/
│   └── supervisord.conf     # Supervisor configuration for services
└── vite/
    └── Dockerfile           # Vite development server
```

## Development Workflow

1. **Start Development Environment**
   ```bash
   docker-compose up -d
   ```

2. **Watch Frontend Changes**
   ```bash
   # Vite akan otomatis reload saat ada perubahan
   # Access di http://localhost:5173
   ```

3. **Make Code Changes**
   - Edit kode di host machine
   - Changes akan ter-sync ke container melalui volumes

4. **Access Services**
   - Laravel App: http://localhost:8000
   - phpMyAdmin: http://localhost:8080
   - Frontend Dev: http://localhost:5173

## Troubleshooting

### Port Conflicts
Jika ada konflik port, edit `docker-compose.yml` dan ubah port mapping:
```yaml
ports:
  - "8001:80"  # Ubah 8000 ke 8001
```

### Permission Issues
```bash
# Fix storage permissions
docker-compose exec app chown -R www:www /var/www/html/storage
docker-compose exec app chmod -R 755 /var/www/html/storage
```

### Database Connection Issues
```bash
# Restart database containers
docker-compose restart mysql mysql-users mysql-ticketing mysql-payments mysql-reviews mysql-inbox

# Check database logs
docker-compose logs mysql
```

### Clear Everything and Start Fresh
```bash
# Stop and remove containers, networks, and volumes
docker-compose down -v --remove-orphans

# Remove all images
docker-compose build --no-cache

# Start fresh
docker-compose up -d
```

## Environment Variables

File `.env.docker` berisi konfigurasi khusus untuk Docker environment:
- Database hosts menggunakan container names (mysql, mysql-users, etc.)
- Redis host menggunakan container name (redis)
- Port configurations sesuai dengan docker-compose mapping

## Production Considerations

Setup ini dioptimalkan untuk **development**. Untuk production:
1. Gunakan environment variables yang secure
2. Setup SSL/HTTPS
3. Gunakan external database services
4. Optimize Docker images
5. Setup proper logging dan monitoring
6. Implement proper backup strategies
