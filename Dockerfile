FROM composer as vendor

WORKDIR /tmp/

COPY database/ database/
COPY composer*.json ./

RUN composer install \
    --ignore-platform-reqs \
    --no-interaction \
    --no-plugins \
    --no-scripts \
    --prefer-dist

FROM php:7.4-fpm

RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    locales \
    zip \
    jpegoptim optipng pngquant gifsicle \
    vim \
    unzip \
    git \
    curl \
    libpq-dev

RUN docker-php-ext-install pdo pdo_pgsql pgsql
RUN docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql

COPY . ./api/

COPY --from=vendor /tmp/vendor ./api/vendor

WORKDIR ./api/

RUN php artisan key:generate --force

RUN php artisan config:cache
RUN php artisan route:cache

CMD php artisan serve --host=0.0.0.0 --port=8000