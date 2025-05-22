FROM php:8.4-apache

RUN apt-get update && \
    apt-get install -y libzip-dev zip unzip git whois netbase && \
    docker-php-ext-install pdo pdo_mysql

RUN a2enmod rewrite

WORKDIR /var/www/html

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

EXPOSE 80
