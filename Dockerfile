FROM composer/composer:2 as vendor
LABEL org.opencontainers.image.authors="img@myli.page"
WORKDIR /tmp/
COPY composer.json composer.json
COPY composer.lock composer.lock
RUN composer install \
    --no-interaction \
    --prefer-dist


FROM php:8.1.6-apache as php-server
COPY --from=vendor /tmp/vendor/ /var/www/html/vendor/
COPY . /var/www/html
RUN chown -R www-data:www-data /var/www/html

ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
