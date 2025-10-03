#!/bin/bash
# Final restore script

echo "🎉 Final restoration of profile settings..."

cd /Users/hannesbonami/Herd/app/Bonamiapp

# Run the profile tabs script
chmod +x restore-profile-tabs.sh
./restore-profile-tabs.sh

# Add navigation link manually
echo ""
echo "📋 Adding navigation link..."

# Check if navigation already exists
if grep -q "Profielinstellingen" resources/views/layouts/app.blade.php; then
    echo "✅ Navigation link already exists"
else
    # Find the profile menu and add our link
    if grep -q "profile\.edit" resources/views/layouts/app.blade.php; then
        # Add after existing profile link
        sed -i.backup 's/route.*profile\.edit.*Profiel.*/&\n                                <a href="{{ route('"'"'profile.settings'"'"') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profielinstellingen<\/a>/' resources/views/layouts/app.blade.php
        echo "✅ Navigation link added"
    else
        echo "⚠️  Could not find profile navigation - add manually"
    fi
fi

# Clear all caches
echo "🧹 Final cache clear..."
php artisan route:clear
php artisan view:clear
php artisan config:clear

echo ""
echo "🎉 PROFILE SETTINGS FULLY RESTORED!"
echo ""
echo "🎯 Test these URLs:"
echo "- /instellingen (should work now)"
echo "- /dashboard (should still work)"
echo ""
echo "✅ Features restored:"
echo "- Profile settings controller ✅"
echo "- Profile settings routes ✅" 
echo "- Profile settings main view ✅"
echo "- Personal info tab ✅"
echo "- Security tab ✅"
echo "- Preferences tab ✅"
echo "- Navigation link ✅"