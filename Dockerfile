FROM php:8-fpm
RUN apt-get update && apt-get install -y sendmail git libzip-dev zip && docker-php-ext-install mysqli pdo pdo_mysql

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

WORKDIR /api

COPY composer.* ./

RUN composer install
