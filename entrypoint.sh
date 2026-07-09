#!/bin/sh
set -e

touch .env

echo "=== Generation cles JWT ==="
mkdir -p config/jwt
if [ ! -f config/jwt/private.pem ]; then
    openssl genrsa -out config/jwt/private.pem 4096
    openssl rsa -in config/jwt/private.pem -pubout -out config/jwt/public.pem
fi

echo "=== Cache clear ==="
php bin/console cache:clear --env=prod

echo "=== Sync schema ==="
php bin/console doctrine:schema:update --force --env=prod || true

echo "=== Demarrage serveur port $PORT ==="
exec php -S 0.0.0.0:${PORT:-8080} -t public