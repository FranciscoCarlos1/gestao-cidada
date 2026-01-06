#!/usr/bin/env bash
set -e
# This script converts this template folder into a full Laravel app (if you didn't clone a full skeleton).
# It uses Composer to download Laravel skeleton and then overlays our app files.
#
# Usage:
#   cd backend
#   bash scaffold.sh

tmpdir=".tmp_laravel"
rm -rf "$tmpdir"
composer create-project laravel/laravel "$tmpdir" --no-interaction

# Copy our custom app code over the fresh skeleton
rsync -a --delete --exclude ".env" "$tmpdir"/ ./
rm -rf "$tmpdir"

echo "Laravel skeleton created. Now run:"
echo "  cp .env.example .env"
echo "  composer install"
echo "  php artisan migrate --seed"
