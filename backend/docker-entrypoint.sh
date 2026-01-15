#!/usr/bin/env bash
set -e

if [ ! -f ".env" ]; then
  cp .env.example .env
fi

if [ ! -d "vendor" ]; then
  composer install --no-interaction --prefer-dist
fi

php artisan key:generate --force || true
php artisan migrate --force || true
php artisan db:seed --force || true

exec "$@"
