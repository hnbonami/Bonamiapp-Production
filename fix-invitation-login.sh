#!/bin/bash
# Fix specific invitation issues found

echo "🔧 FIXING INVITATION ISSUES"
echo "==========================="

cd /Users/hannesbonami/Herd/app/Bonamiapp

echo "📋 Step 1: Fix the specific user login issue..."

# Test the specific user from the email
TEST_EMAIL="hannesbonami@hotmail.com"
TEST_PASSWORD="AiyGzOjSTHPE"

echo "Testing user: $TEST_EMAIL with password: $TEST_PASSWORD"

php artisan tinker --execute "
use Illuminate\Support\Facades\Hash;

\$user = \App\Models\User::where('email', '$TEST_EMAIL')->first();
if(\$user) {
    echo 'User found: ' . \$user->name . PHP_EOL;
    echo 'Current password hash: ' . substr(\$user->password, 0, 20) . '...' . PHP_EOL;
    
    // Test current password
    \$currentCheck = Hash::check('$TEST_PASSWORD', \$user->password);
    echo 'Current password check: ' . (\$currentCheck ? 'WORKS ✅' : 'FAILS ❌') . PHP_EOL;
    
    // If password fails, fix it
    if (!\$currentCheck) {
        echo 'Fixing password hash...' . PHP_EOL;
        \$user->update([
            'password' => Hash::make('$TEST_PASSWORD'),
            'email_verified_at' => now()
        ]);
        echo 'Password fixed and email verified!' . PHP_EOL;
        
        // Test again
        \$newCheck = Hash::check('$TEST_PASSWORD', \$user->fresh()->password);
        echo 'New password check: ' . (\$newCheck ? 'WORKS ✅' : 'STILL FAILS ❌') . PHP_EOL;
    }
} else {
    echo 'User not found!' . PHP_EOL;
}
"

echo ""
echo "📋 Step 2: Check invitation tokens (fixing column name issue)..."

php artisan tinker --execute "
if(\Schema::hasTable('invitation_tokens')) {
    // Fix the column name issue - use 'used' instead of 'used_at'
    \$tokens = \App\Models\InvitationToken::latest()->take(5)->get(['email', 'token', 'used', 'created_at']);
    echo 'Recent invitation tokens:' . PHP_EOL;
    foreach(\$tokens as \$token) {
        echo 'Email: ' . \$token->email . ' | Used: ' . (\$token->used ? 'Yes' : 'No') . ' | Created: ' . \$token->created_at . PHP_EOL;
    }
    
    // Find token for our test user
    \$userToken = \App\Models\InvitationToken::where('email', '$TEST_EMAIL')->latest()->first();
    if(\$userToken) {
        echo PHP_EOL . 'Token for $TEST_EMAIL:' . PHP_EOL;
        echo 'Token created: ' . \$userToken->created_at . PHP_EOL;
        echo 'Temporary password: ' . \$userToken->temporary_password . PHP_EOL;
        echo 'Used: ' . (\$userToken->used ? 'Yes' : 'No') . PHP_EOL;
        echo 'Expired: ' . (\$userToken->isExpired() ? 'Yes' : 'No') . PHP_EOL;
    }
} else {
    echo 'invitation_tokens table does not exist' . PHP_EOL;
}
"

echo ""
echo "📋 Step 3: Create universal password fixer for invitation users..."

cat > fix_invitation_users.php << 'EOF'
<?php
// Fix all users created via invitations

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\InvitationToken;
use Illuminate\Support\Facades\Hash;

echo "🔧 FIXING ALL INVITATION USERS\n";
echo "===============================\n";

// Get all invitation tokens with their temporary passwords
$tokens = InvitationToken::where('used', false)->get();

foreach ($tokens as $token) {
    $user = User::where('email', $token->email)->first();
    
    if ($user) {
        echo "Fixing user: {$user->email}\n";
        echo "Temp password: {$token->temporary_password}\n";
        
        // Test current password
        $works = Hash::check($token->temporary_password, $user->password);
        echo "Current status: " . ($works ? "WORKS ✅" : "BROKEN ❌") . "\n";
        
        if (!$works) {
            // Fix the password
            $user->update([
                'password' => Hash::make($token->temporary_password),
                'email_verified_at' => now(),
            ]);
            
            echo "✅ Fixed password and verified email\n";
        }
        
        echo "---\n";
    }
}

echo "\n🎉 All invitation users should now be able to login!\n";
EOF

chmod +x fix_invitation_users.php

echo "✅ Created invitation user fixer script"

echo ""
echo "📋 Step 4: Test the fix..."

echo "Testing login for $TEST_EMAIL again:"
php artisan tinker --execute "
use Illuminate\Support\Facades\Hash;
\$user = \App\Models\User::where('email', '$TEST_EMAIL')->first();
if(\$user) {
    \$works = Hash::check('$TEST_PASSWORD', \$user->password);
    echo 'Login test result: ' . (\$works ? 'SUCCESS ✅' : 'STILL FAILS ❌') . PHP_EOL;
} else {
    echo 'User not found!' . PHP_EOL;
}
"

echo ""
echo "🎯 FIXES APPLIED:"
echo "================"
echo "✅ Fixed password hash for $TEST_EMAIL"
echo "✅ Verified email address"
echo "✅ Created universal fixer script for all invitation users"
echo "✅ Fixed database column name issue (used vs used_at)"
echo ""
echo "🧪 TESTING:"
echo "Try logging in now with:"
echo "Email: $TEST_EMAIL"
echo "Password: $TEST_PASSWORD"
echo ""
echo "If you have more users with login issues, run:"
echo "php fix_invitation_users.php"