# Base PHP image
FROM php:8.2-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    bash \
    git \
    unzip \
    icu-dev \
    libxml2-dev \
    oniguruma-dev \
    postgresql-dev \
    nodejs \
    npm \
    zip \
    autoconf \
    g++ \
    make \
    supervisor

# PHP extensions
RUN docker-php-ext-install pdo pdo_pgsql mbstring intl bcmath xml

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy only composer files to leverage Docker cache
COPY ../composer.json composer.lock ./

# Install dependencies without running scripts
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Copy full application code
COPY .. .

# Now run post-install scripts manually
RUN php artisan package:discover --ansi

# Set permissions for storage & cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Expose PHP-FPM port
EXPOSE 9000

CMD ["php-fpm"]
