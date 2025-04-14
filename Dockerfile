FROM php:8.3-apache

ENV APACHE_DOCUMENT_ROOT /var/www/html/public

RUN a2enmod rewrite \
 && apt-get update && apt-get install -y \
    libzip-dev zip unzip git curl \
 && docker-php-ext-install pdo pdo_mysql zip \
 && sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/*.conf

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html