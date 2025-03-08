# Use PHP 8.2 with FPM (without Apache)
FROM php:8.2-fpm

# Install system dependencies and required PHP extensions
# RUN apt-get update && apt-get install -y \
#     unzip curl libzip-dev libpng-dev libjpeg-dev libfreetype6-dev libonig-dev libxml2-dev git \
#     && docker-php-ext-install pdo pdo_mysql zip gd intl \
#     && apt-get clean && rm -rf /var/lib/apt/lists/*
# Install system dependencies and required PHP extensions
RUN apt-get update && apt-get install -y \
    unzip curl libzip-dev libpng-dev libjpeg-dev libfreetype6-dev libonig-dev libxml2-dev git \
    && docker-php-ext-install pdo pdo_mysql mysqli zip gd intl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer globally
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy only composer files first (to optimize build caching)
COPY composer.json composer.lock ./

# Install PHP dependencies before copying the full app
RUN composer install --no-dev --optimize-autoloader --prefer-dist

# Copy application files with correct ownership
COPY --chown=www-data:www-data . .

# Set correct permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

RUN chown -R www-data:www-data /var/www/html/public \
    && chmod -R 755 /var/www/html/public


# Ensure .env exists for CodeIgniter
RUN cp .env.example .env || true
RUN php spark key:generate

RUN php spark cache:clear || true
RUN chmod -R 777 /var/www/html/writable

RUN php spark db:reset || true


# Expose PHP-FPM port
EXPOSE 9000

# Start PHP-FPM
CMD ["php-fpm"]
