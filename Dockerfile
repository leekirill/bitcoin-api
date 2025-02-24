FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    libicu-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    pkg-config \
    libzip-dev \
    sqlite3 \
    libsqlite3-dev \
    && docker-php-ext-install \
    intl \
    opcache \
    xml \
    zip \
    pdo \
    pdo_sqlite \
    && docker-php-ext-enable \
    intl \
    opcache \
    xml \
    zip

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/app