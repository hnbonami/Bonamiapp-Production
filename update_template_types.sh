#!/bin/bash

# Update templates tabel type enum om meer opties toe te staan

echo "🔧 Update templates tabel type enum..."

DB_USER="Hannes"
DB_PASS="Hannes1986"
DB_NAME="Bonamisportcoaching"

mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" << 'EOF'
-- Update type enum to include more options
ALTER TABLE templates MODIFY COLUMN type ENUM('email', 'rapport', 'brief', 'bikefit', 'inspanningstest', 'algemeen') NOT NULL DEFAULT 'email';
EOF

echo "✅ Type enum bijgewerkt!"
echo ""
echo "📊 Controleer nieuwe structuur:"
mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "DESCRIBE templates;" 2>/dev/null

echo ""
echo "🧹 Cache legen..."
php artisan cache:clear