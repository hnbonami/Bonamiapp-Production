#!/bin/bash

# Emergency Fix Script voor Medewerker problemen

echo "🚨 Emergency fix voor Medewerker opslaan..."

# Backup huidige database
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/Users/hannesbonami/Backups/bonami_db"
mkdir -p "$BACKUP_DIR"

echo "💾 Backup maken..."
mysqldump -u Hannes -pHannes1986 Bonamisportcoaching --single-transaction --routines --triggers --no-tablespaces > "$BACKUP_DIR/emergency_backup_$DATE.sql" 2>/dev/null

# Check welke kolommen bestaan in medewerkers tabel
echo "📊 Controleren welke kolommen bestaan..."
mysql -u Hannes -pHannes1986 Bonamisportcoaching -e "DESCRIBE medewerkers;" > /tmp/medewerkers_columns.txt

echo "✅ Bestaande kolommen in medewerkers tabel:"
cat /tmp/medewerkers_columns.txt | awk '{print $1}' | grep -v "Field"

# Cache legen
echo "🧹 Cache legen..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear

echo "✅ Emergency fix voltooid!"
echo "💡 Probeer nu een medewerker op te slaan in je browser"