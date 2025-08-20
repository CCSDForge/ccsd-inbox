FROM composer:2.7 as composer
FROM php:7.4.3-fpm

COPY --from=composer /usr/bin/composer /usr/bin/composer

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        libzip-dev \
        zip \
        unzip \
        libicu-dev \
        ssh \
        libssl-dev \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

RUN pecl install zip curl pecl_http\
    && docker-php-ext-install pdo_mysql zip \
    && docker-php-ext-enable  pdo_mysql zip

ADD entrypoint.sh /usr/local/entrypoint
RUN chmod +x /usr/local/entrypoint

ENTRYPOINT ["/usr/local/entrypoint"]