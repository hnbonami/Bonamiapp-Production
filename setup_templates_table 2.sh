#!/bin/bash

# Maak templates tabel aan voor sjablonen functionaliteit

echo "🔧 Maak templates tabel aan..."

DB_USER="Hannes"
DB_PASS="Hannes1986"
DB_NAME="Bonamisportcoaching"

# Controleer of templates tabel bestaat
mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "DESCRIBE templates;" 2>/dev/null

if [ $? -ne 0 ]; then
    echo "📊 Templates tabel bestaat niet, aanmaken..."
    
    mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" << 'EOF'
CREATE TABLE IF NOT EXISTS templates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    type VARCHAR(50) NOT NULL DEFAULT 'algemeen',
    description TEXT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL
);

-- Voeg wat voorbeeld sjablonen toe
INSERT INTO templates (name, content, type, description, created_at, updated_at) VALUES
('Bikefit Rapport', '<h1>Bikefit Rapport</h1>\n<p>Klant: {{klant_naam}}</p>\n<p>Datum: {{datum}}</p>\n<h2>Metingen</h2>\n<p>{{metingen}}</p>\n<h2>Aanbevelingen</h2>\n<p>{{aanbevelingen}}</p>', 'bikefit', 'Standaard sjabloon voor bikefit rapporten', NOW(), NOW()),
('Inspanningstest Rapport', '<h1>Inspanningstest Rapport</h1>\n<p>Klant: {{klant_naam}}</p>\n<p>Datum: {{datum}}</p>\n<h2>Resultaten</h2>\n<p>{{resultaten}}</p>\n<h2>Analyse</h2>\n<p>{{analyse}}</p>', 'inspanningstest', 'Standaard sjabloon voor inspanningstests', NOW(), NOW());
EOF

    echo "✅ Templates tabel aangemaakt met voorbeeld sjablonen!"
else
    echo "✅ Templates tabel bestaat al!"
fi

echo ""
echo "📊 Templates in database:"
mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "SELECT id, name, type FROM templates;" 2>/dev/null

echo ""
echo "🧹 Cache legen..."
php artisan cache:clear
php artisan route:clear

echo "✅ Sjablonen functionaliteit klaar!"