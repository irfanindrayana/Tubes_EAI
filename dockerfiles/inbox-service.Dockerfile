# Inbox Service Dockerfile - Optimized for Security and Performance
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
COPY docker/php/inbox-service-php.ini /usr/local/etc/php/conf.d/inbox-service.ini

# Create application user
RUN addgroup -g 1000 -S www && adduser -u 1000 -S www -G www

# Set working directory
WORKDIR /var/www/html

# Copy only necessary files for Inbox Service
COPY --chown=www:www composer.json composer.lock ./
COPY --chown=www:www artisan ./
COPY --chown=www:www bootstrap/ ./bootstrap/
COPY --chown=www:www config/ ./config/
COPY --chown=www:www database/migrations/ ./database/migrations/

# Copy Inbox Service specific files
COPY --chown=www:www app/Models/Message.php ./app/Models/
COPY --chown=www:www app/Models/MessageRecipient.php ./app/Models/
COPY --chown=www:www app/Models/Notification.php ./app/Models/
COPY --chown=www:www app/Services/Inbox/ ./app/Services/Inbox/
COPY --chown=www:www app/Contracts/InboxServiceInterface.php ./app/Contracts/
COPY --chown=www:www app/Http/Controllers/InboxController.php ./app/Http/Controllers/
COPY --chown=www:www app/Http/Controllers/Api/V1/InboxApiController.php ./app/Http/Controllers/Api/V1/
COPY --chown=www:www app/Services/Http/InboxServiceClient.php ./app/Services/Http/
COPY --chown=www:www app/Providers/MicroserviceServiceProvider.php ./app/Providers/
COPY --chown=www:www app/Providers/ValidationServiceProvider.php ./app/Providers/
COPY --chown=www:www routes/inbox-service.php ./routes/
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

# Configure Nginx for Inbox Service
COPY docker/nginx/inbox-service.conf /etc/nginx/http.d/default.conf

# Configure Supervisor for Inbox Service
COPY docker/supervisor/inbox-service.conf /etc/supervisor/conf.d/inbox-service.conf

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
