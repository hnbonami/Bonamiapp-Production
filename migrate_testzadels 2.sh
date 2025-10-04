# Reset en voer testzadels migratie uit
php artisan migrate:rollback --step=1
php artisan migrate --path=database/migrations/2025_09_30_203000_create_testzadels_table.php

echo "✅ Testzadels tabel aangemaakt (gefixed)"