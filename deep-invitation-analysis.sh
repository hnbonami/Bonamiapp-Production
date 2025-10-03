#!/bin/bash
# Deep analysis of existing invitation system

echo "🔍 DEEP INVITATION SYSTEM ANALYSIS"
echo "=================================="

cd /Users/hannesbonami/Herd/app/Bonamiapp

echo "1️⃣ CHECKING INVITATION TOKEN MODEL:"
echo "===================================="
if [ -f "app/Models/InvitationToken.php" ]; then
    echo "InvitationToken model content:"
    cat app/Models/InvitationToken.php
else
    echo "❌ InvitationToken model not found"
fi

echo ""
echo "2️⃣ CHECKING INVITATION MIGRATION:"
echo "================================="
if [ -f "database/migrations/2025_09_26_000001_create_invitation_tokens_table.php" ]; then
    echo "Invitation tokens migration:"
    cat database/migrations/2025_09_26_000001_create_invitation_tokens_table.php
else
    echo "❌ Invitation migration not found"
fi

echo ""
echo "3️⃣ CHECKING KLANTEN CONTROLLER INVITE METHOD:"
echo "=============================================="
if [ -f "app/Http/Controllers/KlantenController.php" ]; then
    echo "KlantenController invite method:"
    grep -n -A20 -B5 "function invite\|public function invite" app/Http/Controllers/KlantenController.php
else
    echo "❌ KlantenController not found"
fi

echo ""
echo "4️⃣ CHECKING RECENT INVITATION TOKENS:"
echo "====================================="
php artisan tinker --execute "
if(\Schema::hasTable('invitation_tokens')) {
    \$tokens = \App\Models\InvitationToken::latest()->take(5)->get(['email', 'token', 'used_at', 'created_at']);
    echo 'Recent invitation tokens:' . PHP_EOL;
    foreach(\$tokens as \$token) {
        echo 'Email: ' . \$token->email . ' | Used: ' . (\$token->used_at ? 'Yes' : 'No') . ' | Created: ' . \$token->created_at . PHP_EOL;
    }
} else {
    echo 'invitation_tokens table does not exist' . PHP_EOL;
}
"

echo ""
echo "5️⃣ CHECKING USERS WITH INVITATION ISSUES:"
echo "========================================"
echo "Enter email of user with login issues (or press Enter to skip):"
read test_email

if [ ! -z "$test_email" ]; then
    echo "Detailed analysis for $test_email:"
    php artisan tinker --execute "
    \$user = \App\Models\User::where('email', '$test_email')->first();
    if(\$user) {
        echo 'User found:' . PHP_EOL;
        echo '  Name: ' . \$user->name . PHP_EOL;
        echo '  Email: ' . \$user->email . PHP_EOL;
        echo '  Role: ' . (\$user->role ?? 'No role') . PHP_EOL;
        echo '  Email verified: ' . (\$user->email_verified_at ? 'Yes (' . \$user->email_verified_at . ')' : 'No') . PHP_EOL;
        echo '  Password set: ' . (\$user->password ? 'Yes' : 'No') . PHP_EOL;
        echo '  Password hash length: ' . strlen(\$user->password) . PHP_EOL;
        echo '  Created: ' . \$user->created_at . PHP_EOL;
        
        // Check if there's an invitation token
        if(\Schema::hasTable('invitation_tokens')) {
            \$token = \App\Models\InvitationToken::where('email', '$test_email')->latest()->first();
            if(\$token) {
                echo '  Latest invitation: ' . \$token->created_at . PHP_EOL;
                echo '  Token used: ' . (\$token->used_at ? 'Yes' : 'No') . PHP_EOL;
            } else {
                echo '  No invitation token found' . PHP_EOL;
            }
        }
    } else {
        echo 'User not found in database!' . PHP_EOL;
    }
    "
fi

echo ""
echo "6️⃣ TESTING PASSWORD VERIFICATION:"
echo "================================="
echo "Let's test password hashing for the problematic user..."
if [ ! -z "$test_email" ]; then
    echo "Enter the password that should work for $test_email:"
    read -s test_password
    
    php artisan tinker --execute "
    use Illuminate\Support\Facades\Hash;
    \$user = \App\Models\User::where('email', '$test_email')->first();
    if(\$user && \$user->password) {
        \$plainPassword = '$test_password';
        \$hashedPassword = \$user->password;
        
        echo 'Testing password for: $test_email' . PHP_EOL;
        echo 'Password check result: ' . (Hash::check(\$plainPassword, \$hashedPassword) ? 'MATCH ✅' : 'NO MATCH ❌') . PHP_EOL;
        echo 'Hash starts with: ' . substr(\$hashedPassword, 0, 10) . '...' . PHP_EOL;
        
        if(!Hash::check(\$plainPassword, \$hashedPassword)) {
            echo 'Problem found: Password does not match hash!' . PHP_EOL;
            echo 'This suggests the password was not properly hashed when created.' . PHP_EOL;
        }
    } else {
        echo 'User not found or no password set.' . PHP_EOL;
    }
    "
fi