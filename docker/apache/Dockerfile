FROM php:8.1-apache

# GNUBoard5 requires these PHP extensions and Apache mod_rewrite for clean URLs.
RUN apt-get update && apt-get install -y --no-install-recommends \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libzip-dev \
        unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" gd mysqli pdo pdo_mysql zip \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

# GNUBoard5 source is vendored under ./www in this repository.
COPY www/ .

RUN chown -R www-data:www-data /var/www/html
