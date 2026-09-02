FROM php:8.1-apache

# GNUBoard5 requires these PHP extensions and Apache mod_rewrite for clean URLs.
RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
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

# Pull the official GNUBoard5 source at build time so the repository itself
# never has to carry the upstream codebase.
ARG GNUBOARD5_REF=master
RUN git clone --depth 1 --branch "${GNUBOARD5_REF}" https://github.com/gnuboard/g5.git . \
    && rm -rf .git

RUN chown -R www-data:www-data /var/www/html
