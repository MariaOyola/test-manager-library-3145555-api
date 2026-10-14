FROM composer:2 AS vendor

WORKDIR /app
COPY composer.json ./
RUN composer install --no-interaction --prefer-dist --no-dev --no-scripts --no-progress

FROM php:8.3-cli-alpine

WORKDIR /app

RUN apk add --no-cache postgresql-dev \
    && docker-php-ext-install pdo pdo_pgsql

COPY --from=vendor /app/vendor ./vendor
COPY . .

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
