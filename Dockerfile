FROM composer/composer:2 as vendor

WORKDIR /tmp/
COPY composer.json composer.json
COPY composer.lock composer.lock
RUN composer install \
    --no-interaction \
    --prefer-dist


FROM php:8.0.0-apache as php-server

COPY --from=vendor /tmp/vendor/ /var/www/html/vendor/
COPY . /var/www/html
RUN chown -R www-data:www-data /var/www/html
WORKDIR /var/www/html

MAINTAINER tslmy