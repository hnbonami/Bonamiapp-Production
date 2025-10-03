#!/bin/bash
# Diagnose user invitation and login issues

echo "🔍 USER INVITATION & LOGIN DIAGNOSIS"
echo "====================================="

cd /Users/hannesbonami/Herd/app/Bonamiapp

echo "1️⃣ CHECKING USER INVITATION SYSTEM:"
echo "==================================="

echo "Looking for invitation-related files:"
find . -name "*.php" -exec grep -l -i "invite\|invitation" {} \; | head -10

echo ""
echo "2️⃣ CHECKING USER MODEL & AUTHENTICATION:"
echo "========================================"

if [ -f "app/Models/User.php" ]; then
    echo "User model exists. Checking for relevant methods:"
    grep -n -A3 -B3 "password\|invite\|verification" app/Models/User.php
else
    echo "❌ User model not found"
fi

echo ""
echo "3️⃣ CHECKING INVITATION CONTROLLERS:"
echo "==================================="

echo "Looking for invitation controllers:"
find app/Http/Controllers -name "*Invite*" -o -name "*User*" -o -name "*Klant*" | head -5

# Check for invitation methods in controllers
echo ""
echo "Checking for invitation methods:"
find app/Http/Controllers -name "*.php" -exec grep -l "invite" {} \;

echo ""
echo "4️⃣ CHECKING DATABASE STRUCTURE:"
echo "==============================="

echo "Checking users table structure:"
php artisan tinker --execute "
\$columns = \Schema::getColumnListing('users');
foreach(\$columns as \$column) {
    echo \$column . PHP_EOL;
}
"

echo ""
echo "5️⃣ CHECKING RECENT USERS & PASSWORDS:"
echo "====================================="

echo "Checking last 3 created users (without showing sensitive data):"
php artisan tinker --execute "
\$users = \App\Models\User::latest()->take(3)->get(['id', 'name', 'email', 'created_at', 'email_verified_at']);
foreach(\$users as \$user) {
    echo 'ID: ' . \$user->id . ' | Email: ' . \$user->email . ' | Created: ' . \$user->created_at . ' | Verified: ' . (\$user->email_verified_at ? 'Yes' : 'No') . PHP_EOL;
}
"

echo ""
echo "6️⃣ TESTING PASSWORD VERIFICATION:"
echo "================================="

echo "Enter email of a user that has login issues:"
read -p "Email: " test_email

if [ ! -z "$test_email" ]; then
    echo "Checking user details for $test_email:"
    php artisan tinker --execute "
    \$user = \App\Models\User::where('email', '$test_email')->first();
    if(\$user) {
        echo 'User found: ' . \$user->name . PHP_EOL;
        echo 'Email verified: ' . (\$user->email_verified_at ? 'Yes' : 'No') . PHP_EOL;
        echo 'Account active: ' . (isset(\$user->active) ? (\$user->active ? 'Yes' : 'No') : 'Unknown field') . PHP_EOL;
        echo 'Password set: ' . (\$user->password ? 'Yes' : 'No') . PHP_EOL;
        echo 'Password length: ' . strlen(\$user->password) . PHP_EOL;
    } else {
        echo 'User not found!' . PHP_EOL;
    }
    "
fi

echo ""
echo "7️⃣ RECOMMENDATIONS:"
echo "=================="
echo "Common issues with invitations:"
echo "✅ Password not properly hashed when creating user"
echo "✅ Email not verified after invitation"
echo "✅ User account not activated"
echo "✅ Wrong password format in email"
echo "✅ Case sensitivity issues"
echo "✅ Special characters in password"