#!/bin/bash
# Check which admin view is being loaded and fix it

echo "🔍 CHECKING ADMIN VIEW LOADING"
echo "=============================="

cd /Users/hannesbonami/Herd/app/Bonamiapp

echo "📋 Available admin views:"
echo "admin.staff-notes-overview.blade.php (981 bytes):"
if [ -f "resources/views/admin/staff-notes-overview.blade.php" ]; then
    head -10 resources/views/admin/staff-notes-overview.blade.php
fi

echo ""
echo "admin.staff-notes.overview.blade.php (4488 bytes - this should have your database tools):"
if [ -f "resources/views/admin/staff-notes/overview.blade.php" ]; then
    head -20 resources/views/admin/staff-notes/overview.blade.php
fi

echo ""
echo "📋 The issue: adminOverview() is calling 'admin.staff-notes.overview'"
echo "But Laravel might be loading the wrong view file!"

echo ""
echo "📋 Laravel view precedence:"
echo "1. resources/views/admin/staff-notes-overview.blade.php (smaller file)"
echo "2. resources/views/admin/staff-notes/overview.blade.php (your database tools)"

echo ""
echo "🔧 SOLUTION: Make sure we load the correct view with database tools"
echo "We need to either:"
echo "1. Delete the conflicting view"
echo "2. Change the view path in adminOverview()"
echo "3. Use the specific view that has your database tools"

echo ""
echo "Which file has your database import/export buttons?"
echo "Checking for 'Klanten Toevoegen' text..."

if grep -q "Klanten Toevoegen" resources/views/admin/staff-notes/overview.blade.php 2>/dev/null; then
    echo "✅ Database tools found in: admin/staff-notes/overview.blade.php"
    echo "This is the file we want to load!"
else
    echo "❌ Database tools NOT found in admin/staff-notes/overview.blade.php"
fi

if grep -q "Klanten Toevoegen" resources/views/admin/staff-notes-overview.blade.php 2>/dev/null; then
    echo "✅ Database tools found in: admin/staff-notes-overview.blade.php"
else
    echo "❌ Database tools NOT found in admin/staff-notes-overview.blade.php"
fi