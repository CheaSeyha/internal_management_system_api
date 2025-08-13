#!/bin/bash

# Clear Laravel cache & config
php artisan cache:clear
php artisan config:clear

# Run database migrations
php artisan migrate --force

# Generate JWT secret if it doesn't exist
if [ ! -f .env ]; then
    cp .env.example .env
    php artisan key:generate
    php artisan jwt:secret --force
fi

# Start Apache
exec apache2-foreground