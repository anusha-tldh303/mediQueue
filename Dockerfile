FROM php:8.2-apache

RUN docker-php-ext-install pdo_mysql \
    && a2enmod rewrite

ENV DB_HOST=host.docker.internal \
    DB_NAME=doctor_booking_system \
    DB_USER=root \
    DB_PASS=

WORKDIR /var/www/html

COPY . /var/www/html

RUN chown -R www-data:www-data /var/www/html

EXPOSE 80