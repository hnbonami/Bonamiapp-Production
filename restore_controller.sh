#!/bin/bash

echo "🔄 Herstellen van werkende SjablonenController..."

cd /Users/hannesbonami/Desktop/Bonamiapp

# Get the latest working controller (try different commit patterns)
echo "📂 Proberen verschillende commits..."

# Try to restore from HEAD~1, HEAD~2, etc until we find a working one
for i in {1..10}; do
    echo "🔍 Trying HEAD~$i..."
    if git show HEAD~$i:app/Http/Controllers/SjablonenController.php > /tmp/test_controller.php 2>/dev/null; then
        echo "✅ Found controller in HEAD~$i"
        # Check if it looks like a valid PHP file
        if grep -q "class SjablonenController" /tmp/test_controller.php; then
            echo "📋 Restoring controller from HEAD~$i..."
            git show HEAD~$i:app/Http/Controllers/SjablonenController.php > app/Http/Controllers/SjablonenController.php
            echo "✅ Controller restored!"
            break
        fi
    fi
done

echo "🎯 Controller should now be restored from a working commit!"