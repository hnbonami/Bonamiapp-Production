#!/bin/bash
echo "🔄 Performing hard reset to commit ffb51cc..."
cd /Users/hannesbonami/Desktop/Bonamiapp

# Hard reset to specific commit
git reset --hard ffb51cc

# Force push if needed (uncomment if you want to update remote)
# git push --force-with-lease origin main

echo "✅ Hard reset completed!"
echo "📂 Repository is now at commit ffb51cc"
echo "⚠️  All changes after this commit have been permanently lost"