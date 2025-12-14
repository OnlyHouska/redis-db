FROM php:8.4-fpm

RUN apt-get update \
 && apt-get install -y --no-install-recommends \
      libxml2-dev libzip-dev zip unzip git curl zlib1g-dev \
 && docker-php-ext-install pdo_mysql xml zip dom \
 && pecl install redis \
 && docker-php-ext-enable redis \
 && rm -rf /var/lib/apt/lists/*

COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-custom.ini
COPY docker/php/php-fpm.conf /usr/local/etc/php-fpm.d/zz-docker.conf
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf

RUN mkdir -p /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache

WORKDIR /var/www/html

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
