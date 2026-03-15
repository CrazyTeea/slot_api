FROM php:8.4-fpm

ARG UID=1000
ARG GID=1000


RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    wget \
    libzip-dev \
    libpng-dev \
    libicu-dev \
    libxml2-dev \
    libonig-dev


RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    zip \
    gd \
    intl \
    xml \
    mbstring

# Xdebug
RUN pecl install xdebug && docker-php-ext-enable xdebug

COPY php.ini /usr/local/etc/php/php.ini

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer


# Меняем UID/GID у существующего www-data (самый чистый способ)
RUN groupmod -o -g ${GID} www-data && \
    usermod -o -u ${UID} -g www-data www-data && \
    mkdir -p /home/www-data && \
    chown -R www-data:www-data /home/www-data && \
    mkdir -p /var/www/storage /var/www/bootstrap/cache && \
    chown -R www-data:www-data /var/www

WORKDIR /var/www

USER www-data

EXPOSE 9000

CMD ["php-fpm"]
