FROM php:8.2-fpm-alpine

RUN apk add --no-cache \
    libpq-dev \
    icu-dev \
    libzip-dev \
    oniguruma-dev \
    linux-headers \
    su-exec \
    $PHPIZE_DEPS

RUN docker-php-ext-configure pgsql -with-pgsql=/usr/local \
    && docker-php-ext-install -j$(nproc) \
        pdo_pgsql \
        pgsql \
        intl \
        zip \
        opcache \
        mbstring \
        bcmath \
        pcntl

RUN pecl install redis && docker-php-ext-enable redis

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY docker/php/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

COPY . /var/www/html
RUN chown -R www-data:www-data /var/www/html

EXPOSE 9000

ENTRYPOINT ["/entrypoint.sh"]
