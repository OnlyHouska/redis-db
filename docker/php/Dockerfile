FROM php:8.4-apache

# Apache mod_rewrite
RUN a2enmod rewrite

# PHP extensions
RUN apt-get update && apt-get install -y \
    zip \
    unzip \
    git \
    && docker-php-ext-install pdo pdo_mysql

# Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Nastavení DocumentRoot
WORKDIR /var/www/html

# Nastavení práv
RUN chown -R www-data:www-data /var/www/html
