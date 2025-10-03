#!/bin/bash
# Find the original admin staff-notes tools page

echo "🔍 FINDING ORIGINAL ADMIN TOOLS PAGE"
echo "===================================="

cd /Users/hannesbonami/Herd/app/Bonamiapp

echo "1️⃣ CHECKING ADMIN VIEWS:"
echo "========================"
echo "Admin staff-notes related views:"
find resources/views/admin -name "*staff*" -o -name "*note*" | head -10

echo ""
echo "📋 Content of admin staff-notes views:"
ls -la resources/views/admin/staff-notes* 2>/dev/null || echo "No direct staff-notes files in admin folder"

if [ -d "resources/views/admin/staff-notes" ]; then
    echo ""
    echo "Contents of admin/staff-notes directory:"
    ls -la resources/views/admin/staff-notes/
fi

echo ""
echo "2️⃣ LOOKING FOR DATABASE TOOLS:"
echo "=============================="
echo "Searching for database/bikefit/klanten upload/download tools in views:"
find resources/views -name "*.blade.php" -exec grep -l -i "upload\|download\|database\|bikefit.*export\|klanten.*export" {} \; | head -10

echo ""
echo "3️⃣ CHECKING FOR ADMIN OVERVIEW VIEW:"
echo "===================================="
if [ -f "resources/views/admin/staff-notes-overview.blade.php" ]; then
    echo "✅ Found admin/staff-notes-overview.blade.php"
    echo ""
    echo "First 30 lines of the file:"
    head -30 resources/views/admin/staff-notes-overview.blade.php
else
    echo "❌ admin/staff-notes-overview.blade.php not found"
fi

echo ""
echo "4️⃣ CHECKING STAFFNOTECONTROLLER FOR ADMIN METHODS:"
echo "=================================================="
echo "Looking for admin-related methods in StaffNoteController:"
grep -n -A5 -B5 -i "admin\|overview\|export\|upload\|download" app/Http/Controllers/StaffNoteController.php

echo ""
echo "5️⃣ CHECKING FOR SEPARATE ADMIN CONTROLLER:"
echo "=========================================="
echo "Looking for admin controllers that might handle the tools:"
find app/Http/Controllers -name "*Admin*" | head -5

echo ""
echo "6️⃣ WHAT WE NEED TO DO:"
echo "======================"
echo "We need to either:"
echo "1. Create the missing adminOverview() method in StaffNoteController"
echo "2. Find the correct admin tools view and connect it"
echo "3. Restore the database upload/download functionality"