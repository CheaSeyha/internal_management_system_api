#!/bin/bash

# Copy .env.example to .env if it doesn't exist
if [ ! -f .env ]; then
    cp .env.example .env
fi

# Generate Laravel application key (only if not set)
if grep -q 'APP_KEY=' .env; then
    if grep -q 'APP_KEY=base64:................' .env; then
        php artisan key:generate --ansi --force
    fi
else
    php artisan key:generate --ansi --force
fi

# Clear Laravel cache & config
php artisan cache:clear
php artisan config:clear

# Generate JWT secret if not set
if ! grep -q 'JWT_SECRET=' .env; then
    php artisan jwt:secret --force
fi

# Run database migrations
php artisan migrate --force

# Start Apache
exec apache2-foreground