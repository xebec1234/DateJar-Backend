FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git curl unzip libpq-dev libonig-dev libzip-dev zip \
    && docker-php-ext-install pdo pdo_pgsql mbstring zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy app files
COPY . .

# Ensure storage & cache permissions
RUN chown -R www-data:www-data storage bootstrap/cache

# Composer install with increased memory & skip scripts for safety
RUN php -d memory_limit=512M /usr/bin/composer install --no-dev --optimize-autoloader --no-scripts

# Expose port for Render
EXPOSE 8000

# Start Laravel
CMD php artisan serve --host=0.0.0.0 --port=$PORT
