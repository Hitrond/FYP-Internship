FROM laravelsail/php84-composer:latest

ENV COMPOSER_ALLOW_SUPERUSER=1

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libpq-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo_pgsql \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

# Keep Composer dependencies inside Docker's Linux filesystem. Reading the
# host-mounted vendor directory through Docker Desktop is extremely slow.
COPY composer.json composer.lock ./
RUN composer install \
        --no-interaction \
        --prefer-dist \
        --optimize-autoloader \
        --no-scripts \
    && mkdir -p \
        bootstrap/cache \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/testing \
        storage/framework/views

RUN sha256sum composer.lock | cut -d ' ' -f 1 > vendor/.composer-lock.sha256
