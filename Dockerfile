# Use official PHP 8.2 with Apache
FROM php:8.2-apache

# Set working directory
WORKDIR /var/www/html

# Install system dependencies and enable required PHP extensions
RUN apt-get update && apt-get install -y \
    unzip curl libzip-dev libpng-dev libonig-dev libxml2-dev git \
    && docker-php-ext-install pdo pdo_mysql zip gd

# Install Composer globally
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application files
COPY . .

# Install dependencies using Composer
RUN composer install --no-dev --optimize-autoloader

# Enable Apache mod_rewrite for CodeIgniter
RUN a2enmod rewrite

# Set permissions
RUN chown -R www-data:www-data /var/www/html

# Expose the port Apache runs on
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
