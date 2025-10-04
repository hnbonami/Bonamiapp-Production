#!/bin/bash

# Fix dubbele rol veld en voeg toegangsrechten toe

echo "🔧 Fix dubbele rol veld en voeg toegangsrechten toe..."

DB_USER="Hannes"
DB_PASS="Hannes1986"
DB_NAME="Bonamisportcoaching"

echo "📊 Controleer huidige kolommen..."
mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "DESCRIBE medewerkers;" | grep -E "(rol|toegang|access)"

echo ""
echo "🔧 Verwijder dubbele rol kolom en voeg toegangsrechten toe..."

mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" << 'EOF'

-- Verwijder de net toegevoegde rol kolom (de dubbele)
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE table_name = 'medewerkers' 
     AND table_schema = 'Bonamisportcoaching' 
     AND column_name = 'rol' 
     AND ORDINAL_POSITION > (SELECT ORDINAL_POSITION FROM INFORMATION_SCHEMA.COLUMNS 
                            WHERE table_name = 'medewerkers' 
                            AND table_schema = 'Bonamisportcoaching' 
                            AND column_name = 'functie')) > 0,
    'ALTER TABLE medewerkers DROP COLUMN rol',
    'SELECT "No duplicate rol column to drop"'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Voeg toegangsrechten kolom toe
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE table_name = 'medewerkers' 
     AND table_schema = 'Bonamisportcoaching' 
     AND column_name = 'toegangsrechten') = 0,
    'ALTER TABLE medewerkers ADD COLUMN toegangsrechten TEXT NULL',
    'SELECT "toegangsrechten column already exists"'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Controleer of we een echte rol kolom hebben (niet de dubbele)
SELECT 'Checking for existing rol column:' as status;
SELECT COLUMN_NAME, ORDINAL_POSITION 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE table_name = 'medewerkers' 
AND table_schema = 'Bonamisportcoaching' 
AND COLUMN_NAME LIKE '%rol%';

EOF

echo "✅ Database fix voltooid!"
echo ""
echo "📊 Controleer resultaat:"
mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "DESCRIBE medewerkers;" | grep -E "(rol|afdeling|salaris|toegang)"

echo ""
echo "🧹 Cache legen..."
php artisan cache:clear
php artisan config:clear