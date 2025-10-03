#!/bin/bash
# Commit and push the link functionality feature

echo "🚀 Committing and pushing link functionality..."

cd /Users/hannesbonami/Herd/app/Bonamiapp

# Add all changes
git add .

# Commit with descriptive message
git commit -m "✨ Add clickable tile functionality

- Add link_url and open_in_new_tab fields to dashboard content
- Users can now add website links to tiles
- Tiles with links become clickable and open URLs
- Added form fields in create/edit views
- Updated controller validation and database migration
- Link functionality preserves edit/delete button functionality

Features:
- Optional URL field for each tile
- Choice to open link in new tab or same window
- Visual indication for clickable tiles
- Maintains existing tile functionality"

if [ $? -eq 0 ]; then
    echo "✅ Commit successful!"
    
    # Push to remote
    echo "📤 Pushing to remote repository..."
    git push origin main
    
    if [ $? -eq 0 ]; then
        echo "🎉 Successfully pushed to remote!"
        echo ""
        echo "📋 Summary of changes:"
        echo "- Added clickable tile functionality"
        echo "- Users can now add website links to dashboard tiles"
        echo "- Tiles become clickable when a URL is provided"
        echo "- Link opens in new tab or same window based on user choice"
        echo ""
        echo "🔗 Link functionality is now live!"
    else
        echo "❌ Push failed. Check your remote repository settings."
    fi
else
    echo "❌ Commit failed. Check for any issues."
fi

echo ""
echo "📊 Current git status:"
git status --short