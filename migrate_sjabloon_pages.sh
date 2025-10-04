#!/bin/bash

echo "🔧 Fixing migration issue en running only sjabloon_pages migration..."

cd /Users/hannesbonami/Desktop/Bonamiapp

# Check if sjabloon_pages table already exists
echo "📋 Checking if sjabloon_pages table exists..."

# Try to migrate only the specific file we need
echo "🚀 Running sjabloon_pages migration..."
php artisan migrate --path=database/migrations/2025_01_04_154000_create_sjabloon_pages_table.php

echo "✅ Migration completed!"
echo "🎯 Now test the sjablonen editor!"