#!/bin/bash

# Database Fix Script - Voeg ontbrekende kolommen toe aan medewerkers tabel

echo "🔧 Database fix voor medewerkers tabel..."

DB_USER="Hannes"
DB_PASS="Hannes1986"
DB_NAME="Bonamisportcoaching"

# Controleer huidige kolommen
echo "📊 Huidige kolommen in medewerkers tabel:"
mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "DESCRIBE medewerkers;" | awk '{print $1}' | grep -v "Field"

echo ""
echo "🔧 Voeg ontbrekende kolommen toe..."

# Voeg kolommen toe als ze niet bestaan
mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" << 'EOF'
-- Voeg rol kolom toe
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE table_name = 'medewerkers' 
     AND table_schema = 'Bonamisportcoaching' 
     AND column_name = 'rol') = 0,
    'ALTER TABLE medewerkers ADD COLUMN rol VARCHAR(255) NULL AFTER functie',
    'SELECT "rol column already exists"'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Voeg afdeling kolom toe
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE table_name = 'medewerkers' 
     AND table_schema = 'Bonamisportcoaching' 
     AND column_name = 'afdeling') = 0,
    'ALTER TABLE medewerkers ADD COLUMN afdeling VARCHAR(255) NULL AFTER rol',
    'SELECT "afdeling column already exists"'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Voeg salaris kolom toe
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE table_name = 'medewerkers' 
     AND table_schema = 'Bonamisportcoaching' 
     AND column_name = 'salaris') = 0,
    'ALTER TABLE medewerkers ADD COLUMN salaris DECIMAL(10,2) NULL AFTER afdeling',
    'SELECT "salaris column already exists"'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Voeg toegangsniveau kolom toe
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE table_name = 'medewerkers' 
     AND table_schema = 'Bonamisportcoaching' 
     AND column_name = 'toegangsniveau') = 0,
    'ALTER TABLE medewerkers ADD COLUMN toegangsniveau VARCHAR(255) NULL AFTER salaris',
    'SELECT "toegangsniveau column already exists"'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
EOF

echo "✅ Database kolommen toegevoegd!"
echo ""
echo "📊 Nieuwe kolommen in medewerkers tabel:"
mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "DESCRIBE medewerkers;" | grep -E "(rol|afdeling|salaris|toegang)"

echo ""
echo "🧹 Cache legen..."
php artisan cache:clear
php artisan config:clear

echo "✅ Database fix voltooid! Test nu je medewerker bewerken."