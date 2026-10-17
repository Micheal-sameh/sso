# syntax=docker/dockerfile:1

########################################
# 1) Build frontend assets (Vite/React)
########################################
FROM node:22-alpine AS frontend
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY vite.config.ts tsconfig.json ./
COPY resources ./resources

ARG VITE_APP_NAME
ENV VITE_APP_NAME=$VITE_APP_NAME

RUN npm run build

########################################
# 2) Install PHP dependencies
########################################
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install \
        --no-dev \
        --no-scripts \
        --no-autoloader \
        --no-interaction \
        --prefer-dist \
        --ignore-platform-req=ext-exif
COPY . .
RUN composer dump-autoload --optimize --no-dev --classmap-authoritative

########################################
# 3) Runtime image: php-fpm + the Laravel scheduler, managed by supervisor
#    (nginx/webserver and MySQL are expected to already run on the host)
########################################
FROM php:8.3-fpm-alpine AS runtime

# Handles build deps, parallel compile, runtime libs and cleanup in one step
COPY --from=mlocati/php-extension-installer:latest /usr/bin/install-php-extensions /usr/local/bin/

# Extensions first (slowest, rarely changes) so this layer stays cached
RUN --mount=type=cache,target=/var/cache/apk \
    install-php-extensions \
        pdo_mysql \
        mbstring \
        bcmath \
        zip \
        intl \
        gd \
        exif \
        pcntl \
        opcache \
        redis

RUN --mount=type=cache,target=/var/cache/apk \
    apk add \
        bash \
        curl \
        git \
        unzip \
        supervisor

COPY --from=vendor /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY --from=vendor /app ./
COPY --from=frontend /app/public/build /opt/frontend-build

RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

COPY docker/php/local.ini /usr/local/etc/php/conf.d/local.ini
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 9000

ENTRYPOINT ["entrypoint.sh"]
CMD ["supervisord", "-c", "/etc/supervisord.conf"]
