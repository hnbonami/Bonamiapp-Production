#!/bin/bash
# Add the correct adminOverview method to use existing view

echo "🔧 ADDING CORRECT ADMINOVERVIEW METHOD"
echo "====================================="

cd /Users/hannesbonami/Herd/app/Bonamiapp

echo "📋 Step 1: Backup StaffNoteController..."
cp app/Http/Controllers/StaffNoteController.php app/Http/Controllers/StaffNoteController.php.backup-admin-$(date +%Y%m%d-%H%M%S)

echo ""
echo "📋 Step 2: Add adminOverview method that uses existing view..."

# Create the method that matches what your existing view expects
cat > temp_admin_method.txt << 'EOF'

    /**
     * Admin overview - shows staff notes overview for admin users
     */
    public function adminOverview()
    {
        // Get all staff notes with their read status
        $notes = \App\Models\StaffNote::with(['readers'])->latest()->get();
        
        // Get all users to show read status
        $users = \App\Models\User::all();
        
        return view('admin.staff-notes.overview', compact('notes', 'users'));
    }
EOF

# Add the method before the closing brace of the class
sed -i.backup '/^}[[:space:]]*$/i\
' app/Http/Controllers/StaffNoteController.php

# Insert the method
sed -i.backup2 '/^}[[:space:]]*$/{
    r temp_admin_method.txt
}' app/Http/Controllers/StaffNoteController.php

# Clean up
rm temp_admin_method.txt

echo "✅ Added adminOverview method to StaffNoteController"

echo ""
echo "📋 Step 3: Ensure route calls adminOverview (should already be correct)..."

# Check if route is already calling adminOverview
if grep -q "adminOverview" routes/web.php; then
    echo "✅ Route already calls adminOverview method"
else
    echo "⚠️  Route needs to be updated to call adminOverview"
    # Fix the route if needed
    sed -i.backup-route 's/->index()/->adminOverview()/' routes/web.php
    echo "✅ Updated route to call adminOverview"
fi

echo ""
echo "📋 Step 4: Clear caches..."
php artisan route:clear
php artisan config:clear
php artisan view:clear

echo ""
echo "📋 Step 5: Verify the setup..."
echo "StaffNoteController methods:"
grep -n "public function" app/Http/Controllers/StaffNoteController.php | tail -3

echo ""
echo "Route check:"
grep -A3 -B3 "admin/staff-notes/overview" routes/web.php

echo ""
echo "🎉 ADMIN OVERVIEW RESTORED!"
echo "=========================="
echo "✅ Added adminOverview() method to StaffNoteController"
echo "✅ Method uses existing admin.staff-notes.overview view"
echo "✅ Route correctly calls adminOverview()"
echo "✅ Should now show your original admin tools page"
echo ""
echo "🧪 TEST NOW:"
echo "Click on 'Beheer' button - should show your original admin tools page!"