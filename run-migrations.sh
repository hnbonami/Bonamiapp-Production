#!/bin/bash
# Run migrations to create the tables
php artisan migrate

# Clear any cached routes/config
php artisan route:clear
php artisan config:clear
php artisan cache:clear