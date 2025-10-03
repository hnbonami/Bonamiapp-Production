#!/bin/bash
# Commit and push dashboard route fix

echo "📝 Preparing commit for dashboard route fix..."

cd /Users/hannesbonami/Herd/app/Bonamiapp

# Check current status
echo "📋 Current git status:"
git status --short

echo ""
echo "📋 Files to be committed:"
git add .
git status --short

echo ""
echo "📝 Creating commit message for dashboard fix..."

# Create detailed commit message
cat > commit_message.txt << 'EOF'
🔧 Fix dashboard route to point to new dashboard-content

🎯 Problem Fixed:
- Sidebar "Dashboard" button was still showing old dashboard view
- Route conflict: old dashboard route was taking precedence

🔧 Technical Changes:
- Updated /dashboard route from old function to DashboardContentController@index
- Fixed sidebar navigation to use {{ route('dashboard') }} instead of hardcoded URLs
- Ensured proper route priority and resolution

📱 Navigation Updates:
- Desktop sidebar: /dashboard → {{ route('dashboard') }}
- Mobile navigation: /dashboard → {{ route('dashboard') }}
- Both now correctly point to new dashboard-content with tiles

🧹 Cache Management:
- Cleared route, config, view, and application caches
- Verified route resolution works correctly

✅ Result:
- Dashboard button now shows modern dashboard-content interface
- Old dashboard still accessible via /dashboard-oud if needed
- Consistent navigation experience across desktop and mobile

Files modified:
- routes/web.php (dashboard route definition)
- resources/views/layouts/app.blade.php (navigation links)
EOF

echo "📋 Commit message preview:"
cat commit_message.txt

echo ""
read -p "🤔 Do you want to commit with this message? (y/n): " confirm

if [[ $confirm == [yY] || $confirm == [yY][eE][sS] ]]; then
    echo "📝 Committing dashboard route fix..."
    git commit -F commit_message.txt
    
    if [ $? -eq 0 ]; then
        echo "✅ Commit successful!"
        
        echo ""
        read -p "🚀 Push to remote repository? (y/n): " push_confirm
        
        if [[ $push_confirm == [yY] || $push_confirm == [yY][eE][sS] ]]; then
            echo "🚀 Pushing dashboard fix to remote..."
            git push origin main
            
            if [ $? -eq 0 ]; then
                echo "🎉 Successfully pushed dashboard fix!"
                echo ""
                echo "📋 Summary:"
                echo "✅ Dashboard route fixed"
                echo "✅ Sidebar navigation updated"
                echo "✅ Code committed locally"
                echo "✅ Changes pushed to remote repository"
                echo ""
                echo "🎯 What's working now:"
                echo "- Dashboard button shows new dashboard-content"
                echo "- Navigation uses proper Laravel routes"
                echo "- Both desktop and mobile navigation fixed"
                echo "- Cache cleared for immediate effect"
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
echo "🏁 Dashboard fix commit script completed!"