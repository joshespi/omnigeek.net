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
