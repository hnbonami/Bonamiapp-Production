#!/bin/bash
# Laravel 11+ compatible login redirect fix

echo "🔧 LARAVEL 11+ LOGIN REDIRECT & DASHBOARD FIX"
echo "=============================================="

cd /Users/hannesbonami/Herd/app/Bonamiapp

echo "📋 Step 1: Check Laravel version and setup..."
php artisan --version

echo ""
echo "📋 Step 2: Backup files..."
cp routes/web.php routes/web.php.backup-laravel11-$(date +%Y%m%d-%H%M%S)

echo ""
echo "📋 Step 3: Rename old dashboard view to avoid confusion..."

if [ -f "resources/views/dashboard.blade.php" ]; then
    echo "📄 Found old dashboard.blade.php - renaming..."
    mv resources/views/dashboard.blade.php resources/views/dashboard-legacy.blade.php
    echo "✅ Renamed: dashboard.blade.php → dashboard-legacy.blade.php"
else
    echo "ℹ️  dashboard.blade.php not found (already renamed?)"
fi

echo ""
echo "📋 Step 4: Check login redirect configuration in Laravel 11+..."

# In Laravel 11+, check bootstrap/app.php for redirect configuration
if [ -f "bootstrap/app.php" ]; then
    echo "📄 Checking bootstrap/app.php for redirect config..."
    grep -n -A5 -B5 "home\|dashboard\|redirect" bootstrap/app.php || echo "No redirect config found in bootstrap/app.php"
fi

# Check auth.php routes for redirect
echo ""
echo "📄 Checking auth routes..."
if [ -f "routes/auth.php" ]; then
    grep -n -A3 -B3 "home\|dashboard\|redirect" routes/auth.php || echo "No specific redirect in auth.php"
fi

echo ""
echo "📋 Step 5: Ensure /dashboard-legacy route exists for old dashboard access..."

# Add or update legacy dashboard route
if ! grep -q "/dashboard-legacy\|dashboard-oud" routes/web.php; then
    echo "Adding legacy dashboard route..."
    
    # Find the line with the main dashboard route and add legacy route after it
    sed -i.backup '/DashboardContentController.*dashboard/a\\n// Legacy dashboard access\nRoute::get("/dashboard-legacy", function () {\n    return view("dashboard-legacy");\n})->middleware(["auth", "verified"])->name("dashboard.legacy");' routes/web.php
    
    echo "✅ Added /dashboard-legacy route"
else
    echo "✅ Legacy dashboard route already exists"
fi

echo ""
echo "📋 Step 6: Update any references to old dashboard view..."

# Update any remaining references in routes
if grep -q "return view('dashboard')" routes/web.php; then
    sed -i.backup2 "s/return view('dashboard')/return view('dashboard-legacy')/" routes/web.php
    echo "✅ Updated route references to use dashboard-legacy"
fi

echo ""
echo "📋 Step 7: Laravel 11+ Login Redirect Fix..."

# For Laravel 11+, the redirect is usually handled in the LoginResponse
echo "Creating login redirect customization..."

# Check if LoginResponse exists
if [ ! -d "app/Http/Responses" ]; then
    mkdir -p app/Http/Responses
fi

# Create custom LoginResponse for Laravel 11+
cat > app/Http/Responses/LoginResponse.php << 'EOF'
<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request): JsonResponse|RedirectResponse
    {
        // Always redirect to dashboard after login (regardless of role)
        return $request->wantsJson()
            ? new JsonResponse([], 200)
            : redirect()->route('dashboard');
    }
}
EOF

echo "✅ Created custom LoginResponse"

echo ""
echo "📋 Step 8: Register LoginResponse in AppServiceProvider..."

# Add to AppServiceProvider
if [ -f "app/Providers/AppServiceProvider.php" ]; then
    # Check if binding already exists
    if ! grep -q "LoginResponse" app/Providers/AppServiceProvider.php; then
        sed -i.backup '/public function register/a\\n        // Custom login redirect\n        $this->app->singleton(\Laravel\Fortify\Contracts\LoginResponse::class, \App\Http\Responses\LoginResponse::class);' app/Providers/AppServiceProvider.php
        echo "✅ Added LoginResponse binding to AppServiceProvider"
    else
        echo "✅ LoginResponse binding already exists"
    fi
fi

echo ""
echo "📋 Step 9: Clear caches..."
php artisan route:clear
php artisan config:clear
php artisan view:clear
php artisan optimize:clear

echo ""
echo "📋 Step 10: Verify setup..."
echo "Dashboard routes:"
php artisan route:list | grep dashboard

echo ""
echo "🎉 LARAVEL 11+ LOGIN REDIRECT FIXED!"
echo ""
echo "✅ Configuration:"
echo "- Login always redirects to /dashboard (new dashboard-content)"
echo "- /dashboard → DashboardContentController@index (modern interface)"
echo "- /dashboard-legacy → dashboard-legacy.blade.php (old interface)"
echo "- Old dashboard.blade.php renamed to dashboard-legacy.blade.php"
echo "- Custom LoginResponse forces redirect to new dashboard"
echo ""
echo "🧪 TEST: Login with any role → should go to new dashboard!"
echo "🔗 Old dashboard access: /dashboard-legacy"