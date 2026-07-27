FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
    bash \
    git \
    curl \
    libpng-dev \
    libzip-dev \
    postgresql-dev \
    oniguruma-dev \
    autoconf \
    g++ \
    make \
    && docker-php-ext-install pdo pdo_pgsql pgsql mbstring zip exif pcntl gd bcmath opcache \
    && pecl install redis && docker-php-ext-enable redis

WORKDIR /var/www

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY . /var/www

RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress || true

RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

EXPOSE 9000

CMD ["php-fpm"]
