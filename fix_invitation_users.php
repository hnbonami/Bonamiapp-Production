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
