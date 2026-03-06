# PulseGuard – Production Dockerfile (PHP + Queue worker)
# Multi-stage: app image

FROM php:8.2-cli-alpine AS base

RUN apk add --no-cache \
    libzip-dev \
    zip \
    unzip \
    icu-dev \
    libpq-dev \
    oniguruma-dev \
    linux-headers

RUN docker-php-ext-configure intl && \
    docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    pdo_pgsql \
    zip \
    intl \
    opcache \
    pcntl

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock* ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

COPY . .
RUN composer dump-autoload --optimize

# Production image
FROM base AS app

ENV APP_ENV=production
ENV APP_DEBUG=false

RUN php artisan config:cache 2>/dev/null || true
RUN php artisan route:cache 2>/dev/null || true

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
