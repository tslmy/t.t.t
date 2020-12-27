FROM php:8.0.0-apache
MAINTAINER tslmy

COPY . /var/www/html
WORKDIR /var/www/html
