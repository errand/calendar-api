FROM php:8.4-fpm

RUN docker-php-ext-install pdo pdo_mysql mysqli \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html

COPY composer.json .
RUN composer install

COPY app/ ./app/
COPY public/ ./public/

COPY docker/php/php.ini /usr/local/etc/php/

RUN chown -R www-data:www-data /var/www/html

EXPOSE 9000

CMD ["php-fpm"]