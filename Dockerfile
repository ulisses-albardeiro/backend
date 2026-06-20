FROM php:8.4-cli-alpine

RUN apk add --no-cache \
    git \
    unzip \
    curl \
    libzip-dev \
    oniguruma-dev \
    libpng-dev \
    icu-dev

RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    fileinfo \
    zip \
    mbstring \
    opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY docker/php/php.ini $PHP_INI_DIR/conf.d/dev.ini

RUN curl -1sLf 'https://github.com/symfony-cli/symfony-cli/releases/latest/download/symfony-cli_linux_amd64.tar.gz' \
    | tar -xz -C /usr/local/bin symfony

WORKDIR /var/www/html

COPY composer.json composer.lock symfony.lock ./
RUN composer install --no-scripts --no-autoloader --prefer-dist

COPY . .
RUN composer dump-autoload --optimize

RUN chown -R www-data:www-data var

EXPOSE 8000

CMD ["symfony", "serve", "--no-tls", "--port=8000", "--allow-http"]
