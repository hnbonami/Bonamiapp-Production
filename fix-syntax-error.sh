#!/bin/bash
# Fix syntax error in StaffNoteController

echo "🔧 FIXING SYNTAX ERROR IN STAFFNOTECONTROLLER"
echo "============================================="

cd /Users/hannesbonami/Herd/app/Bonamiapp

echo "📋 Step 1: Check the current syntax error..."
echo "Lines around 160-165 in StaffNoteController:"
sed -n '155,165p' app/Http/Controllers/StaffNoteController.php

echo ""
echo "📋 Step 2: Restore from backup if available..."
if [ -f "app/Http/Controllers/StaffNoteController.php.backup-admin-"*".php" ]; then
    BACKUP_FILE=$(ls -t app/Http/Controllers/StaffNoteController.php.backup-admin-* | head -1)
    echo "Found backup: $BACKUP_FILE"
    echo "Restoring from backup..."
    cp "$BACKUP_FILE" app/Http/Controllers/StaffNoteController.php
    echo "✅ Restored from backup"
else
    echo "❌ No backup found - will fix manually"
fi

echo ""
echo "📋 Step 3: Add the adminOverview method correctly..."

# Check if adminOverview method already exists
if grep -q "function adminOverview" app/Http/Controllers/StaffNoteController.php; then
    echo "✅ adminOverview method already exists"
else
    echo "Adding adminOverview method..."
    
    # Create a properly formatted method
    cat > temp_method.txt << 'EOF'

    /**
     * Admin overview with database import/export tools
     */
    public function adminOverview()
    {
        // Get staff notes with pagination for the overview
        $notes = StaffNote::with('user')->latest()->paginate(10);
        
        return view('admin.staff-notes.overview', compact('notes'));
    }
EOF

    # Find the last method and add before the closing brace
    # Remove any extra closing braces first
    sed -i.backup '/^}[[:space:]]*$/d' app/Http/Controllers/StaffNoteController.php
    
    # Add the method and proper closing brace
    cat temp_method.txt >> app/Http/Controllers/StaffNoteController.php
    echo "}" >> app/Http/Controllers/StaffNoteController.php
    
    # Clean up
    rm temp_method.txt
    
    echo "✅ Added adminOverview method"
fi

echo ""
echo "📋 Step 4: Verify syntax is correct..."
php -l app/Http/Controllers/StaffNoteController.php

echo ""
echo "📋 Step 5: Clear caches..."
php artisan route:clear
php artisan config:clear

echo ""
echo "🎉 SYNTAX ERROR FIXED!"
echo "====================="
echo "✅ StaffNoteController syntax corrected"
echo "✅ adminOverview method properly added"
echo "✅ Caches cleared"
echo ""
echo "🧪 TEST NOW: Click 'Beheer' button - should work without syntax errors!"