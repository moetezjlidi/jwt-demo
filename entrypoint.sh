#!/bin/sh
set -e

echo "=== Generation cles JWT ==="
mkdir -p config/jwt
if [ ! -f config/jwt/private.pem ]; then
    openssl genrsa -out config/jwt/private.pem 4096
    openssl rsa -in config/jwt/private.pem -pubout -out config/jwt/public.pem
fi

echo "=== Cache clear ==="
php bin/console cache:clear --env=prod

echo "=== Migrations ==="
php bin/console doctrine:migrations:migrate --no-interaction --env=prod

echo "=== Demarrage serveur port $PORT ==="
exec php -S 0.0.0.0:${PORT:-8080} -t public