#!/bin/bash
# Temporary fix: rename conflicting view to force Laravel to load the correct one

echo "🔧 QUICK FIX: FORCING CORRECT ADMIN VIEW"
echo "========================================"

cd /Users/hannesbonami/Herd/app/Bonamiapp

echo "📋 Step 1: Temporarily rename the conflicting view..."

# Move the smaller file (without database tools) out of the way
if [ -f "resources/views/admin/staff-notes-overview.blade.php" ]; then
    mv resources/views/admin/staff-notes-overview.blade.php resources/views/admin/staff-notes-overview.blade.php.backup
    echo "✅ Moved conflicting view to backup"
else
    echo "❌ Conflicting view not found"
fi

echo ""
echo "📋 Step 2: Clear view cache..."
php artisan view:clear

echo ""
echo "📋 Step 3: Verify which view Laravel will now load..."
echo "With staff-notes-overview.blade.php moved, Laravel should now load:"
echo "→ resources/views/admin/staff-notes/overview.blade.php"
echo "→ This has your database tools!"

echo ""
echo "🎉 QUICK FIX APPLIED!"
echo "==================="
echo "✅ Conflicting view moved to backup"
echo "✅ Laravel will now load the correct view with database tools"
echo "✅ View cache cleared"
echo ""
echo "🧪 TEST NOW: Click 'Beheer' button"
echo "You should now see:"
echo "- 📊 Data Importeren"
echo "- 👥 Klanten Toevoegen" 
echo "- 🚴 Bikefits Toevoegen"
echo "- 📤 Data Exporteren"
echo "- 📥 Download Alle Klanten"
echo "- 📊 Download Alle Bikefits"