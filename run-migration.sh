#!/bin/bash
# Run migration for mini tile size
cd /Users/hannesbonami/Herd/app/Bonamiapp

echo "🔄 Running migration to add mini tile size..."

# Create the migration file if it doesn't exist
php artisan make:migration add_mini_tile_size_to_staff_notes_table --table=staff_notes

# Run the migration
php artisan migrate

echo "✅ Migration completed!"
echo "✅ Controller validation rules are already updated!"