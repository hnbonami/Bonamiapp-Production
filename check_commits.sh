#!/bin/bash

echo "🔄 Herstellen van werkende SjablonenController uit commit c51e926..."

cd /Users/hannesbonami/Desktop/Bonamiapp

# Try to get the controller from the working commit
echo "📂 Herstellen van SjablonenController..."

# First, let's see what commits we have
echo "🔍 Checking available commits..."
git log --oneline -10

echo ""
echo "🎯 Trying to restore SjablonenController from a working commit..."

# Let's try to find a commit that has the controller working
# We'll try a few different approaches
git log --oneline --grep="sjablonen" -5
git log --oneline --grep="template" -5

echo ""
echo "📋 Available commits shown above."
echo "💡 Please check which commit has the working SjablonenController"
echo "    and run: git show <commit-hash>:app/Http/Controllers/SjablonenController.php"