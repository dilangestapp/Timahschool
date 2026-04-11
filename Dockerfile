FROM node:20-bullseye AS frontend

WORKDIR /app

COPY package*.json ./
RUN if [ -f package-lock.json ]; then npm ci; else npm install; fi

COPY . .
RUN npm run build


FROM composer:2 AS composer_bin


FROM php:8.1-cli-bullseye

WORKDIR /app

RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    zip \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libicu-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer_bin /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock* ./
RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader --no-scripts

COPY . .

COPY --from=frontend /app/public/build ./public/build

RUN composer dump-autoload --optimize --no-dev \
    && mkdir -p storage/framework/cache \
    && mkdir -p storage/framework/sessions \
    && mkdir -p storage/framework/views \
    && mkdir -p storage/logs \
    && mkdir -p bootstrap/cache \
    && mkdir -p /data/public \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 8080

CMD sh -lc '\
    mkdir -p /data/public && \
    rm -rf public/storage && \
    php artisan storage:link || true && \
    php artisan package:discover --ansi || true && \
    php artisan serve --host=0.0.0.0 --port=${PORT:-8080} \
'