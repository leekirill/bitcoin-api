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
    supervisor \
    cron \
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

RUN docker-php-ext-install pcntl

WORKDIR /var/www/app

COPY . /var/www/app

RUN touch /var/log/cron.log && chmod 777 /var/log/cron.log

RUN echo "* * * * * /usr/local/bin/php /var/www/app/bin/console app:update-rate >> /var/log/cron.log 2>&1" | crontab -

COPY ./supervisord.conf /etc/supervisor/conf.d/supervisord.conf

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
