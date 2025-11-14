FROM php:8.2-fpm

# System dependencies
RUN apt-get update && apt-get install -y \
    git curl libzip-dev zip unzip libpng-dev libonig-dev libxml2-dev sqlite3 libsqlite3-dev \
    && docker-php-ext-install pdo_mysql mbstring zip exif pcntl bcmath

# Install Redis extension
RUN pecl install redis \
    && docker-php-ext-enable redis

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

