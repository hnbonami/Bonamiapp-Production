#!/bin/bash

# Script om werkende sjablonen views te herstellen uit commit c51e926

echo "🔄 Herstellen van werkende sjablonen views uit commit c51e926..."

# Ga naar de juiste directory
cd /Users/hannesbonami/Desktop/Bonamiapp

# Haal de werkende sjablonen views op uit de werkende commit
echo "📂 Herstellen van sjablonen views..."
git checkout c51e926 -- resources/views/sjablonen/

# Controleer of het gelukt is
if [ -d "resources/views/sjablonen" ]; then
    echo "✅ Sjablonen views succesvol hersteld!"
    echo "📝 Overzicht van herstelde bestanden:"
    ls -la resources/views/sjablonen/
else
    echo "❌ Fout bij herstellen van sjablonen views"
    exit 1
fi

echo "🎯 Klaar! De werkende sjablonen views zijn hersteld."
echo "💡 Je kunt nu testen door naar /sjablonen te gaan"