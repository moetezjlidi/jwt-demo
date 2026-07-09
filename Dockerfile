FROM php:8.3-cli

RUN apt-get update && apt-get install -y \
    libpq-dev \
    libicu-dev \
    unzip \
    git \
    openssl \
    && docker-php-ext-install pdo pdo_pgsql intl opcache \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts
RUN composer dump-autoload --optimize --no-dev
RUN test -d vendor/symfony/runtime && echo "OK symfony runtime present" || echo "ERREUR symfony runtime MISSING"

COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 8080
ENTRYPOINT ["/entrypoint.sh"] 