#!/bin/sh
# Generate Passport keys only if they don't exist
if [ ! -f storage/oauth-private.key ] || [ ! -f storage/oauth-public.key ]; then
    echo "Generating Passport keys..."
    php artisan passport:keys
fi

# Create storage link only if it doesn't exist
if [ ! -L public/storage ]; then
    echo "Creating storage link..."
    php artisan storage:link
fi
php artisan passport:keys
php artisan config:cache
php artisan route:cache

echo "Starting server..."

php artisan serve --host=0.0.0.0 --port=${PORT:-80}