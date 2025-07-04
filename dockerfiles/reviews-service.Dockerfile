# Reviews Service Dockerfile - Optimized for Security and Performance
FROM php:8.2-fpm-alpine as base

# Install system dependencies
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
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip opcache

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configure PHP for production
COPY docker/php/reviews-service-php.ini /usr/local/etc/php/conf.d/reviews-service.ini

# Create application user
RUN addgroup -g 1000 -S www && adduser -u 1000 -S www -G www

# Set working directory
WORKDIR /var/www/html

# Copy only necessary files for Reviews Service
COPY --chown=www:www composer.json composer.lock ./
COPY --chown=www:www artisan ./
COPY --chown=www:www bootstrap/ ./bootstrap/
COPY --chown=www:www config/ ./config/
COPY --chown=www:www database/migrations/ ./database/migrations/

# Copy Reviews Service specific files (to be implemented)
COPY --chown=www:www app/Models/Review.php ./app/Models/ 2>/dev/null || true
COPY --chown=www:www app/Models/Complaint.php ./app/Models/ 2>/dev/null || true
COPY --chown=www:www app/Services/Reviews/ ./app/Services/Reviews/ 2>/dev/null || true
COPY --chown=www:www app/Contracts/ReviewsServiceInterface.php ./app/Contracts/ 2>/dev/null || true
COPY --chown=www:www app/Http/Controllers/ReviewsController.php ./app/Http/Controllers/ 2>/dev/null || true
COPY --chown=www:www app/Http/Controllers/Api/V1/ReviewsApiController.php ./app/Http/Controllers/Api/V1/ 2>/dev/null || true
COPY --chown=www:www app/Services/Http/ReviewsServiceClient.php ./app/Services/Http/ 2>/dev/null || true
COPY --chown=www:www app/Providers/MicroserviceServiceProvider.php ./app/Providers/
COPY --chown=www:www app/Providers/ValidationServiceProvider.php ./app/Providers/
COPY --chown=www:www routes/reviews-service.php ./routes/ 2>/dev/null || true
COPY --chown=www:www routes/internal-api.php ./routes/
COPY --chown=www:www routes/web.php ./routes/
COPY --chown=www:www routes/console.php ./routes/
COPY --chown=www:www public/ ./public/
COPY --chown=www:www resources/ ./resources/
COPY --chown=www:www storage/ ./storage/

# Install PHP dependencies with optimizations
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist \
    && composer dump-autoload --optimize \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

# Configure Nginx for Reviews Service
COPY docker/nginx/reviews-service.conf /etc/nginx/http.d/default.conf

# Configure Supervisor for Reviews Service
COPY docker/supervisor/reviews-service.conf /etc/supervisor/conf.d/reviews-service.conf

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
