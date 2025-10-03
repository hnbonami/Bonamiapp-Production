#!/bin/bash
# Commit and push profile settings implementation

echo "📝 Preparing commit and push for profile settings..."

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
✨ Implement comprehensive profile settings system

🎯 Features Added:
- Complete ProfileSettingsController with AJAX support
- Modern tabbed interface for profile management
- Personal information management (name, email, phone, address, avatar)
- Security features (password change, 2FA toggle, account deactivation)
- Preferences management (language, privacy, notifications)
- Real-time profile completion tracking
- Responsive design with smooth transitions

🔧 Technical Improvements:
- Added profile settings routes (/instellingen)
- AJAX form submissions for seamless UX
- Image upload handling for avatars
- Form validation with real-time feedback
- Progress tracking for profile completion
- Database migration ready for new user fields

🎨 UI/UX Enhancements:
- Tab-based navigation for settings sections
- Loading states and success/error messages
- Modern card-based layout
- Mobile-responsive design
- Consistent styling with existing app theme

🔗 Integration:
- Navigation menu integration
- Proper route organization
- Fallback handling for missing database fields
- Backward compatibility maintained

Files added/modified:
- app/Http/Controllers/ProfileSettingsController.php
- resources/views/profile/settings.blade.php
- resources/views/profile/tabs/personal.blade.php
- resources/views/profile/tabs/security.blade.php
- resources/views/profile/tabs/preferences.blade.php
- routes/web.php (profile settings routes)
EOF

echo "📋 Commit message preview:"
cat commit_message.txt

echo ""
read -p "🤔 Do you want to commit with this message? (y/n): " confirm

if [[ $confirm == [yY] || $confirm == [yY][eE][sS] ]]; then
    echo "📝 Committing changes..."
    git commit -F commit_message.txt
    
    if [ $? -eq 0 ]; then
        echo "✅ Commit successful!"
        
        echo ""
        read -p "🚀 Push to remote repository? (y/n): " push_confirm
        
        if [[ $push_confirm == [yY] || $push_confirm == [yY][eE][sS] ]]; then
            echo "🚀 Pushing to remote..."
            git push origin main
            
            if [ $? -eq 0 ]; then
                echo "🎉 Successfully pushed to remote!"
                echo ""
                echo "📋 Summary:"
                echo "✅ Profile settings system implemented"
                echo "✅ Code committed locally"
                echo "✅ Changes pushed to remote repository"
                echo ""
                echo "🎯 Next steps:"
                echo "- Test the profile settings at /instellingen"
                echo "- Run database migrations if needed"
                echo "- Consider adding more features as needed"
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
echo "🏁 Script completed!"