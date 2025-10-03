#!/bin/bash
# Fix adminOverview to load the correct database tools view

echo "🔧 FIXING ADMIN VIEW TO SHOW DATABASE TOOLS"
echo "==========================================="

cd /Users/hannesbonami/Herd/app/Bonamiapp

echo "📋 Step 1: Check which view has the database tools..."

# Check which view has the database import/export tools
if grep -q "Klanten Toevoegen\|Data Importeren\|Data Exporteren" resources/views/admin/staff-notes/overview.blade.php 2>/dev/null; then
    echo "✅ Database tools found in: resources/views/admin/staff-notes/overview.blade.php"
    CORRECT_VIEW="admin.staff-notes.overview"
    CORRECT_PATH="resources/views/admin/staff-notes/overview.blade.php"
elif grep -q "Klanten Toevoegen\|Data Importeren\|Data Exporteren" resources/views/admin/staff-notes-overview.blade.php 2>/dev/null; then
    echo "✅ Database tools found in: resources/views/admin/staff-notes-overview.blade.php"
    CORRECT_VIEW="admin.staff-notes-overview"
    CORRECT_PATH="resources/views/admin/staff-notes-overview.blade.php"
else
    echo "❌ Database tools not found in either view!"
    echo "Let's check the content of both files:"
    echo ""
    echo "Content of admin/staff-notes/overview.blade.php:"
    head -30 resources/views/admin/staff-notes/overview.blade.php
    exit 1
fi

echo "Correct view to use: $CORRECT_VIEW"
echo "File path: $CORRECT_PATH"

echo ""
echo "📋 Step 2: Update adminOverview() method to use correct view..."

# Create the corrected adminOverview method
cat > temp_admin_method.php << EOF
    /**
     * Admin overview with database import/export tools
     */
    public function adminOverview()
    {
        // Get staff notes with pagination for the admin tools page
        \$notes = StaffNote::with('user')->latest()->paginate(10);
        
        // Return the correct admin tools view with database import/export buttons
        return view('$CORRECT_VIEW', compact('notes'));
    }
EOF

# Replace the adminOverview method in StaffNoteController
sed -i.backup '/\/\*\*/{
    :a
    n
    /public function adminOverview/,/^    }$/{
        /^    }$/c\
'"$(cat temp_admin_method.php)"'
        b
    }
    ba
}' app/Http/Controllers/StaffNoteController.php

# Clean up
rm temp_admin_method.php

echo "✅ Updated adminOverview() method"

echo ""
echo "📋 Step 3: Clear caches..."
php artisan route:clear
php artisan config:clear
php artisan view:clear

echo ""
echo "📋 Step 4: Show what view is being loaded..."
echo "adminOverview() now loads: $CORRECT_VIEW"
echo "Which should show your database tools with:"
echo "- 👥 Klanten Toevoegen"
echo "- 🚴 Bikefits Toevoegen"
echo "- 📥 Download Alle Klanten"
echo "- 📊 Download Alle Bikefits"

echo ""
echo "🎉 ADMIN DATABASE TOOLS FIXED!"
echo "============================="
echo "✅ adminOverview() now points to the correct view"
echo "✅ Database import/export tools should be visible"
echo "✅ Caches cleared"
echo ""
echo "🧪 TEST NOW: Click 'Beheer' button - should show database tools!"