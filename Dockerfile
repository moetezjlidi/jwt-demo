FROM php:8.3-cli

# Dépendances système + extensions PHP nécessaires
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libicu-dev \
    unzip \
    git \
    openssl \
    && docker-php-ext-install pdo pdo_pgsql intl opcache \
    && rm -rf /var/lib/apt/lists/*

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

# Installer les dépendances PHP (avec scripts pour que Symfony Flex fonctionne normalement)
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Script de démarrage
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 8080
ENTRYPOINT ["/entrypoint.sh"]