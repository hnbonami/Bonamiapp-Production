#!/bin/bash
# Analyze StaffNoteController and staff-notes system

echo "🔍 STAFF NOTES CONTROLLER ANALYSIS"
echo "=================================="

cd /Users/hannesbonami/Herd/app/Bonamiapp

echo "1️⃣ CHECKING STAFFNOTECONTROLLER:"
echo "================================"
if [ -f "app/Http/Controllers/StaffNoteController.php" ]; then
    echo "✅ StaffNoteController exists"
    echo ""
    echo "📋 Available methods in StaffNoteController:"
    grep -n "public function\|private function\|protected function" app/Http/Controllers/StaffNoteController.php
    
    echo ""
    echo "📋 Looking for adminOverview method specifically:"
    grep -n -A10 -B5 "adminOverview" app/Http/Controllers/StaffNoteController.php || echo "❌ adminOverview method NOT FOUND"
    
else
    echo "❌ StaffNoteController does NOT exist"
fi

echo ""
echo "2️⃣ CHECKING STAFF-NOTES ROUTES:"
echo "==============================="
echo "Routes in web.php that mention staff-notes:"
grep -n -A3 -B3 "staff-notes\|staffnotes\|StaffNote" routes/web.php

echo ""
echo "3️⃣ CHECKING PROBLEMATIC ROUTE:"
echo "=============================="
echo "Line 392 in routes/web.php:"
sed -n '390,395p' routes/web.php

echo ""
echo "4️⃣ CHECKING STAFF-NOTES RELATED FILES:"
echo "====================================="
echo "Staff-notes related views:"
find resources/views -name "*staff*" -o -name "*note*" | head -10

echo ""
echo "5️⃣ CHECKING STAFF-NOTES MODELS:"
echo "==============================="
echo "Staff-notes related models:"
find app/Models -name "*Staff*" -o -name "*Note*" | head -5

echo ""
echo "6️⃣ CURRENT ROUTE LIST:"
echo "======================"
echo "Staff-notes related routes:"
php artisan route:list | grep -i "staff\|note" | head -10

echo ""
echo "🎯 DIAGNOSIS:"
echo "============"
echo "The error occurs at routes/web.php:392"
echo "Route calls StaffNoteController::adminOverview() which doesn't exist"
echo "Need to either:"
echo "1. Add the missing adminOverview method"
echo "2. Fix the route to use an existing method"
echo "3. Redirect to a working staff-notes page"