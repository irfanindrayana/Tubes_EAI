# API Gateway Dockerfile - Optimized for Security and Performance
FROM php:8.2-fpm-alpine as base

# Install system dependencies including Node.js
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    oniguruma-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    supervisor \
    nginx \
    mysql-client \
    nodejs \
    npm \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip opcache

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configure PHP for production
COPY docker/php/api-gateway-php.ini /usr/local/etc/php/conf.d/api-gateway.ini

# Create application user
RUN addgroup -g 1000 -S www && adduser -u 1000 -S www -G www

# Set working directory
WORKDIR /var/www/html

# Copy only necessary files for API Gateway
COPY --chown=www:www composer.json composer.lock ./
COPY --chown=www:www package.json package-lock.json ./
COPY --chown=www:www artisan ./
COPY --chown=www:www bootstrap/ ./bootstrap/
COPY --chown=www:www config/ ./config/
COPY --chown=www:www database/migrations/ ./database/migrations/

# Copy API Gateway specific files
COPY --chown=www:www app/GraphQL/ ./app/GraphQL/
COPY --chown=www:www app/Http/Controllers/HomeController.php ./app/Http/Controllers/
COPY --chown=www:www app/Http/Controllers/DashboardController.php ./app/Http/Controllers/
COPY --chown=www:www app/Http/Controllers/AdminController.php ./app/Http/Controllers/
COPY --chown=www:www app/Http/Controllers/Auth/ ./app/Http/Controllers/Auth/
COPY --chown=www:www app/Services/Http/ ./app/Services/Http/
COPY --chown=www:www app/Contracts/ ./app/Contracts/
COPY --chown=www:www app/Providers/ ./app/Providers/
COPY --chown=www:www routes/api-gateway.php ./routes/
COPY --chown=www:www routes/web.php ./routes/
COPY --chown=www:www routes/api.php ./routes/
COPY --chown=www:www graphql/ ./graphql/
COPY --chown=www:www public/ ./public/
COPY --chown=www:www resources/ ./resources/
COPY --chown=www:www storage/ ./storage/
COPY --chown=www:www vite.config.js ./

# Install Node.js dependencies and build assets
RUN npm ci --only=production && npm run build

# Install PHP dependencies with optimizations
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist \
    && composer dump-autoload --optimize \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

# Configure Nginx for API Gateway
COPY docker/nginx/api-gateway.conf /etc/nginx/http.d/default.conf

# Configure Supervisor for API Gateway
COPY docker/supervisor/api-gateway.conf /etc/supervisor/conf.d/api-gateway.conf

# Set secure permissions
RUN chown -R www:www /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache \
    && chmod -R 644 /var/www/html/public \
    && chmod 755 /var/www/html/public

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=40s --retries=3 \
    CMD curl -f http://localhost/health || exit 1

# Switch to non-root user
USER www

# Expose port
EXPOSE 80

# Start services using supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/supervisord.conf"]
