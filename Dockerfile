FROM node:24-alpine AS assets
WORKDIR /app
COPY src/package.json src/package-lock.json ./
RUN npm ci
COPY src/ ./
RUN npm run build && test -f public/build/manifest.json || (echo "ERROR: Vite build produced no manifest.json" && exit 1)

FROM composer:latest AS vendor
WORKDIR /app
COPY src/composer.json src/composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

FROM php:8.5-fpm
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libjpeg-dev libfreetype6-dev libonig-dev libxml2-dev libzip-dev \
    && docker-php-ext-configure gd --with-jpeg --with-freetype \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Run the fpm worker pool as host uid/gid so container-written files stay host-editable
RUN sed -i 's/^user = www-data/user = 1000/; s/^group = www-data/group = 1000/' /usr/local/etc/php-fpm.d/www.conf

WORKDIR /var/www/html

COPY src/ ./
COPY --from=vendor /app/vendor ./vendor
COPY --from=assets /app/public/build ./public/build
RUN mkdir -p bootstrap/cache storage/framework/cache storage/framework/sessions storage/framework/views storage/logs \
    && composer dump-autoload --no-dev --optimize \
    && chown -R 1000:1000 storage bootstrap/cache
