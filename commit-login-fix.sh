#!/bin/bash
# Commit and push Laravel 11+ login redirect fix

echo "📝 Preparing commit for Laravel 11+ login redirect & dashboard naming fix..."

cd /Users/hannesbonami/Herd/app/Bonamiapp

# Check current status
echo "📋 Current git status:"
git status --short

echo ""
echo "📋 Files to be committed:"
git add .
git status --short

echo ""
echo "📝 Creating comprehensive commit message..."

# Create detailed commit message
cat > commit_message.txt << 'EOF'
🔧 Fix login redirect and dashboard naming for Laravel 11+

🎯 Problem Fixed:
- Login sometimes redirected to login page causing 404 errors
- Old and new dashboard files had confusing naming
- No consistent post-login redirect for all user roles

🔧 Laravel 11+ Login Redirect Fix:
- Created custom LoginResponse that always redirects to route('dashboard')
- Registered LoginResponse in AppServiceProvider
- Ensures all users (klant/admin/medewerker) go to new dashboard after login
- Eliminates 404 errors and login page loops

📁 Dashboard File Organization:
- Renamed dashboard.blade.php → dashboard-legacy.blade.php
- Added /dashboard-legacy route for old dashboard access
- Clear separation between old and new dashboard systems
- Prevents confusion when working on "new" vs "old" dashboard

🛡️ Technical Implementation:
- Custom App\Http\Responses\LoginResponse class
- Laravel 11+ compatible (no RouteServiceProvider dependency)
- Proper service container binding in AppServiceProvider
- Route organization and naming cleanup

✅ Result:
- Login always redirects to new dashboard-content interface
- Old dashboard accessible via /dashboard-legacy for reference
- No more 404 errors or login loops
- Clear file naming prevents future confusion
- Consistent user experience across all roles

Files added/modified:
- app/Http/Responses/LoginResponse.php (new)
- app/Providers/AppServiceProvider.php (LoginResponse binding)
- routes/web.php (legacy dashboard route)
- resources/views/dashboard-legacy.blade.php (renamed from dashboard.blade.php)
EOF

echo "📋 Commit message preview:"
cat commit_message.txt

echo ""
read -p "🤔 Do you want to commit with this message? (y/n): " confirm

if [[ $confirm == [yY] || $confirm == [yY][eE][sS] ]]; then
    echo "📝 Committing Laravel 11+ login redirect fix..."
    git commit -F commit_message.txt
    
    if [ $? -eq 0 ]; then
        echo "✅ Commit successful!"
        
        echo ""
        read -p "🚀 Push to remote repository? (y/n): " push_confirm
        
        if [[ $push_confirm == [yY] || $push_confirm == [yY][eE][sS] ]]; then
            echo "🚀 Pushing login redirect fix to remote..."
            git push origin main
            
            if [ $? -eq 0 ]; then
                echo "🎉 Successfully pushed login redirect fix!"
                echo ""
                echo "📋 Summary:"
                echo "✅ Laravel 11+ login redirect implemented"
                echo "✅ Dashboard naming organized"
                echo "✅ Custom LoginResponse created"
                echo "✅ Code committed and pushed"
                echo ""
                echo "🎯 What's now working:"
                echo "- All users redirect to new dashboard after login"
                echo "- No more 404 errors or login loops"
                echo "- Old dashboard accessible via /dashboard-legacy"
                echo "- Clear file organization for future development"
                echo ""
                echo "🧪 Test login with different user roles!"
            else
                echo "❌ Push failed! Check your remote repository settings."
            fi
        else
            echo "⏸️  Changes committed locally but not pushed."
        fi
    else
        echo "❌ Commit failed!"
    fi
else
    echo "⏸️  Commit cancelled."
fi

# Cleanup
rm -f commit_message.txt

echo ""
echo "🏁 Login redirect fix commit script completed!"