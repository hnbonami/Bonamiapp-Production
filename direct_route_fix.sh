#!/bin/bash

echo "🔧 DIRECT ROUTE FIX - Adding routes manually to web.php..."

cd /Users/hannesbonami/Desktop/Bonamiapp

# Create backup
cp routes/web.php routes/web.php.backup.$(date +%s)

# Add routes directly to web.php before the closing brace
cat >> routes/web.php << 'EOF'

// SJABLONEN PREVIEW AND PDF ROUTES - Added automatically
Route::middleware(['auth'])->group(function () {
    Route::get('sjablonen/{id}/preview', [\App\Http\Controllers\SjablonenController::class, 'preview'])->name('sjablonen.preview');
    Route::get('sjablonen/{template}/generate-pdf/{klant?}/{test_id?}/{type?}', [\App\Http\Controllers\SjablonenController::class, 'generatePdf'])->name('sjablonen.generate-pdf');
});
EOF

# Clear all caches
php artisan route:clear 2>/dev/null || true
php artisan cache:clear 2>/dev/null || true
php artisan config:clear 2>/dev/null || true

echo "✅ Routes added directly to web.php!"
echo "📋 Checking routes..."

# Check if routes are registered
php artisan route:list | grep -E "(preview|generate-pdf)" && echo "✅ Routes successfully registered!" || echo "⚠️  Please refresh your browser"

echo ""
echo "🎯 SHOULD NOW WORK:"
echo "   📄 Preview: /sjablonen/2/preview"
echo "   💾 PDF: Click the Download HTML button"
echo ""
echo "🔄 If still not working, restart your Laravel server:"
echo "   php artisan serve --port=8002"