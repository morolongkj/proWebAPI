# Use PHP 8.2 with Apache
FROM php:8.2-apache

# Install system dependencies and required PHP extensions
RUN apt-get update && apt-get install -y \
    unzip curl libzip-dev libpng-dev libjpeg-dev libfreetype6-dev libonig-dev libxml2-dev git \
    && docker-php-ext-install pdo pdo_mysql mysqli zip gd intl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Enable Apache modules for CodeIgniter routing
RUN a2enmod rewrite

# Install Composer globally
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy composer files first (to optimize Docker cache)
COPY composer.json composer.lock ./

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --prefer-dist

# Copy application files with correct ownership
COPY --chown=www-data:www-data . .

# Set Apache DocumentRoot to the CodeIgniter public folder
RUN sed -i -e "s|/var/www/html|/var/www/html/public|g" /etc/apache2/sites-available/000-default.conf

# Set correct permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Ensure writable folder is writable by Apache
RUN chmod -R 777 /var/www/html/writable

# Ensure writable folder is writable by Apache
RUN chown -R www-data:www-data /var/www/html/writable \
    && chmod -R 777 /var/www/html/writable

# Copy .env file if it doesn't exist
RUN cp .env.example .env || true

# CodeIgniter commands to generate app key and clear cache
RUN php spark key:generate || true
RUN php spark cache:clear || true

# Ensure database is reset (optional)
RUN php spark db:reset || true

# Expose Apache port
EXPOSE 80

# Start Apache in foreground
CMD ["apache2-foreground"]
