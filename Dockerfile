FROM php:8.3-cli-alpine AS runtime
WORKDIR /app
RUN apk add --no-cache postgresql-dev libxml2-dev oniguruma-dev unzip git \
    && docker-php-ext-install pdo_pgsql dom xml xmlwriter mbstring
COPY --from=composer/composer:2-bin /composer /usr/local/bin/composer

FROM runtime AS development
COPY . .
RUN composer install --no-interaction --prefer-dist --no-progress
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]

FROM runtime AS production
COPY . .
RUN composer install --no-interaction --prefer-dist --no-dev --no-progress
EXPOSE 8000
CMD ["sh", "-c", "php artisan library:provision-admin && php artisan serve --host=0.0.0.0 --port=8000"]
