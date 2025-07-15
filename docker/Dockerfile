FROM php:8.3-fpm

COPY . /app
WORKDIR /app

RUN apt-get update \
    && apt-get install -y \
    libpq-dev \
    libfreetype-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libwebp-dev \
    libicu-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pdo pdo_pgsql pgsql intl \
    && docker-php-ext-install -j$(nproc) gd