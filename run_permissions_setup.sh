#!/bin/bash

# Check welke migraties al zijn uitgevoerd
echo "=== MIGRATIE STATUS ==="
php artisan migrate:status

echo ""
echo "=== ONZE NIEUWE MIGRATIES FORCEREN ==="

# Forceer onze specifieke migraties
php artisan migrate --path=database/migrations/2024_01_10_000001_create_role_permissions_table.php --force
php artisan migrate --path=database/migrations/2024_01_10_000002_create_role_test_permissions_table.php --force  
php artisan migrate --path=database/migrations/2024_01_10_000003_create_user_login_logs_table.php --force
php artisan migrate --path=database/migrations/2024_01_10_000004_add_tracking_fields_to_users_table.php --force

echo ""
echo "=== SEEDER UITVOEREN ==="
php artisan db:seed --class=RolePermissionsSeeder

echo ""
echo "=== KLAAR! ==="