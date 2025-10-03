#!/bin/bash

# Restore Script voor Bonami App
# Gebruik: ./restore.sh [backup_datum]
# Voorbeeld: ./restore.sh 20250929_163154

BACKUP_DIR="/Users/hannesbonami/Backups/bonami_db"
DB_NAME="Bonamisportcoaching"
DB_USER="Hannes"
DB_PASS="Hannes1986"

echo "🔄 Bonami Database Restore Script"

# Als geen datum gegeven, toon beschikbare backups
if [ -z "$1" ]; then
    echo "📋 Beschikbare backups:"
    ls -lt "$BACKUP_DIR"/bonami_backup_*.sql 2>/dev/null | head -10 | while read line; do
        filename=$(echo $line | awk '{print $9}')
        basename_file=$(basename "$filename")
        datum=$(echo "$basename_file" | sed 's/bonami_backup_\(.*\)\.sql/\1/')
        grootte=$(echo $line | awk '{print $5}')
        echo "  📅 $datum (${grootte} bytes)"
    done
    echo ""
    echo "💡 Gebruik: ./restore.sh [datum]"
    echo "   Voorbeeld: ./restore.sh 20250929_163154"
    exit 1
fi

BACKUP_DATE="$1"
BACKUP_FILE="$BACKUP_DIR/bonami_backup_$BACKUP_DATE.sql"

# Controleer of backup bestaat
if [ ! -f "$BACKUP_FILE" ]; then
    echo "❌ Backup bestand niet gevonden: $BACKUP_FILE"
    exit 1
fi

echo "📁 Backup bestand: $BACKUP_FILE"
echo "📊 Bestand grootte: $(du -h "$BACKUP_FILE" | cut -f1)"
echo ""

# Vraag bevestiging
read -p "⚠️  Weet je zeker dat je de database wilt herstellen? Dit overschrijft huidige data! (y/N): " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "🚫 Restore geannuleerd"
    exit 1
fi

# Maak eerst een backup van huidige database
echo "💾 Huidige database backup maken..."
CURRENT_DATE=$(date +%Y%m%d_%H%M%S)
mysqldump -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" --single-transaction --routines --triggers --no-tablespaces > "$BACKUP_DIR/pre_restore_backup_$CURRENT_DATE.sql" 2>/dev/null

# Restore database
echo "🔄 Database herstellen..."
if mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$BACKUP_FILE" 2>/dev/null; then
    echo "✅ Database succesvol hersteld!"
    echo "📅 Hersteld naar versie: $BACKUP_DATE"
    echo "💾 Pre-restore backup gemaakt: pre_restore_backup_$CURRENT_DATE.sql"
else
    echo "❌ Restore gefaald!"
    exit 1
fi

echo ""
echo "🎉 Restore voltooid! Controleer je applicatie."