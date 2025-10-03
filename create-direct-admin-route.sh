#!/bin/bash
# Create direct route to admin database tools

echo "🔧 CREATING DIRECT ROUTE TO ADMIN DATABASE TOOLS"
echo "==============================================="

cd /Users/hannesbonami/Herd/app/Bonamiapp

echo "📋 Step 1: Create a direct route that bypasses view conflicts..."

# Add a new direct route to web.php that specifically loads the database tools view
cat >> routes/web.php << 'EOF'

// Direct route to admin database tools (bypassing view conflicts)
Route::middleware(['auth', 'verified'])->group(function() {
    Route::get('/admin/database-tools', function() {
        $notes = \App\Models\StaffNote::with('user')->latest()->paginate(10);
        
        // Explicitly load the view file with database tools
        return view()->file(resource_path('views/admin/staff-notes/overview.blade.php'), compact('notes'));
    })->name('admin.database.tools');
});
EOF

echo "✅ Added direct route to admin database tools"

echo ""
echo "📋 Step 2: Update the existing admin route to use the new route..."

# Replace the existing admin route to redirect to our new working route
sed -i.backup 's|return app(\\App\\Http\\Controllers\\StaffNoteController::class)->adminOverview();|return redirect()->route("admin.database.tools");|' routes/web.php

echo "✅ Updated existing admin route to redirect to working route"

echo ""
echo "📋 Step 3: Clear caches..."
php artisan route:clear
php artisan config:clear
php artisan view:clear

echo ""
echo "📋 Step 4: Show new routes..."
php artisan route:list | grep -i "admin\|database"

echo ""
echo "🎉 DIRECT ADMIN DATABASE TOOLS ROUTE CREATED!"
echo "============================================="
echo "✅ New route: /admin/database-tools"
echo "✅ Directly loads: admin/staff-notes/overview.blade.php" 
echo "✅ Bypasses view name conflicts"
echo "✅ Original admin route redirects to working route"
echo ""
echo "🧪 TEST NOW:"
echo "1. Click 'Beheer' button → should redirect to database tools"
echo "2. Or go directly to: /admin/database-tools"
echo ""
echo "You should now see your database import/export tools!"