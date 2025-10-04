#!/bin/bash

echo "🔄 Herstellen van werkende SjablonenController uit commit c51e926..."

cd /Users/hannesbonami/Desktop/Bonamiapp

# Restore the working controller from the specific commit
echo "📂 Herstellen van SjablonenController uit c51e926..."
git show c51e926:app/Http/Controllers/SjablonenController.php > app/Http/Controllers/SjablonenController.php

# Check if it worked
if [ -f "app/Http/Controllers/SjablonenController.php" ]; then
    echo "✅ SjablonenController succesvol hersteld uit commit c51e926!"
    echo "📝 Controller file size: $(wc -l < app/Http/Controllers/SjablonenController.php) lines"
    
    # Show first few lines to confirm it's the right file
    echo "📋 First few lines of restored controller:"
    head -10 app/Http/Controllers/SjablonenController.php
else
    echo "❌ Fout bij herstellen van controller"
    exit 1
fi

echo "🎯 Klaar! De werkende SjablonenController is hersteld."
echo "💡 Test nu de sjablonen editor - alles zou moeten werken!"