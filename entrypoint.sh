#!/bin/sh
php artisan passport:keys
php artisan storage:link || true
php artisan passport:keys
php artisan config:cache
php artisan route:cache

echo "Starting server..."

php artisan serve --host=0.0.0.0 --port=${PORT:-80}