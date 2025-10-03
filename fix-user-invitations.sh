#!/bin/bash
# Fix user invitation and login issues

echo "🔧 FIXING USER INVITATION & LOGIN ISSUES"
echo "========================================"

cd /Users/hannesbonami/Herd/app/Bonamiapp

echo "📋 Step 1: Find invitation-related code..."

# Look for klanten invitation method
if [ -f "app/Http/Controllers/KlantenController.php" ]; then
    echo "Found KlantenController. Checking for invite method:"
    grep -n -A10 -B5 "invite" app/Http/Controllers/KlantenController.php
fi

# Look for other controllers with invite functionality
find app/Http/Controllers -name "*.php" -exec grep -l "invite\|invitation" {} \;

echo ""
echo "📋 Step 2: Common invitation issues and fixes..."

echo "Creating fixed invitation helper script:"

cat > fix_user_password.php << 'EOF'
<?php
// Helper script to fix user password issues

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

// Fix password for specific user
function fixUserPassword($email, $newPassword = null) {
    $user = User::where('email', $email)->first();
    
    if (!$user) {
        echo "User not found: $email\n";
        return false;
    }
    
    // Generate new password if not provided
    if (!$newPassword) {
        $newPassword = 'TempPass' . rand(1000, 9999);
    }
    
    // Update user with properly hashed password and verify email
    $user->update([
        'password' => Hash::make($newPassword),
        'email_verified_at' => now(),
    ]);
    
    echo "Fixed user: $email\n";
    echo "New password: $newPassword\n";
    echo "Email verified: Yes\n";
    
    return $newPassword;
}

// Check if running from command line
if (isset($argv[1])) {
    $email = $argv[1];
    $password = isset($argv[2]) ? $argv[2] : null;
    fixUserPassword($email, $password);
} else {
    echo "Usage: php fix_user_password.php user@example.com [optional_password]\n";
}
EOF

chmod +x fix_user_password.php

echo "✅ Created password fix helper script"

echo ""
echo "📋 Step 3: Create improved invitation method..."

cat > improved_invitation_method.txt << 'EOF'
// Improved invitation method for KlantenController or other controllers

public function invite(Request $request, $klant)
{
    try {
        // Find the klant
        $klant = Klant::findOrFail($klant);
        
        // Check if user already exists
        $user = User::where('email', $klant->email)->first();
        
        if (!$user) {
            // Generate secure password
            $password = 'Bonami' . rand(1000, 9999) . '!';
            
            // Create user with properly hashed password
            $user = User::create([
                'name' => $klant->voornaam . ' ' . $klant->naam,
                'email' => $klant->email,
                'password' => Hash::make($password), // IMPORTANT: Hash the password!
                'role' => 'klant',
                'email_verified_at' => now(), // Auto-verify for invited users
            ]);
            
            $isNewUser = true;
        } else {
            // Generate new password for existing user
            $password = 'Bonami' . rand(1000, 9999) . '!';
            
            // Update existing user
            $user->update([
                'password' => Hash::make($password), // IMPORTANT: Hash the password!
                'email_verified_at' => now(),
            ]);
            
            $isNewUser = false;
        }
        
        // Send invitation email with clear instructions
        Mail::send('emails.klant-invitation', [
            'user' => $user,
            'password' => $password, // Send plain password in email
            'klant' => $klant,
            'loginUrl' => route('login'),
        ], function ($message) use ($user) {
            $message->to($user->email, $user->name)
                    ->subject('Uitnodiging Bonami Sportcoaching');
        });
        
        return back()->with('success', 
            $isNewUser 
                ? "Nieuwe gebruiker aangemaakt en uitnodiging verzonden naar {$klant->email}"
                : "Uitnodiging verzonden naar {$klant->email} (bestaande gebruiker)"
        );
        
    } catch (\Exception $e) {
        \Log::error('Invitation failed: ' . $e->getMessage());
        return back()->withErrors(['error' => 'Uitnodiging verzenden mislukt: ' . $e->getMessage()]);
    }
}
EOF

echo "✅ Created improved invitation method template"

echo ""
echo "📋 Step 4: Create invitation email template..."

mkdir -p resources/views/emails

cat > resources/views/emails/klant-invitation.blade.php << 'EOF'
<!DOCTYPE html>
<html>
<head>
    <title>Uitnodiging Bonami Sportcoaching</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #c8e1eb; padding: 20px; text-align: center; }
        .content { background: #f9f9f9; padding: 30px; }
        .credentials { background: #fff; border: 2px solid #c8e1eb; padding: 20px; margin: 20px 0; border-radius: 5px; }
        .button { display: inline-block; background: #c8e1eb; color: #333; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: bold; }
        .footer { background: #eee; padding: 15px; font-size: 12px; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Welkom bij Bonami Sportcoaching</h1>
        </div>
        
        <div class="content">
            <p>Beste {{ $user->name }},</p>
            
            <p>Je bent uitgenodigd om een account aan te maken bij Bonami Sportcoaching. Hieronder vind je je inloggegevens:</p>
            
            <div class="credentials">
                <h3>📧 Jouw Inloggegevens:</h3>
                <p><strong>Email:</strong> {{ $user->email }}</p>
                <p><strong>Wachtwoord:</strong> <code style="background: #f0f0f0; padding: 2px 5px; font-size: 16px;">{{ $password }}</code></p>
            </div>
            
            <p><strong>⚠️ Belangrijk:</strong></p>
            <ul>
                <li>Kopieer het wachtwoord exact zoals hierboven staat</li>
                <li>Let op hoofdletters en kleine letters</li>
                <li>Gebruik geen spaties voor of na het wachtwoord</li>
            </ul>
            
            <p style="text-align: center; margin: 30px 0;">
                <a href="{{ $loginUrl }}" class="button">Nu Inloggen</a>
            </p>
            
            <p><small>Je kunt je wachtwoord na het eerste inloggen wijzigen in je profielinstellingen.</small></p>
        </div>
        
        <div class="footer">
            <p>Bonami Sportcoaching | Heb je problemen met inloggen? Neem contact met ons op.</p>
        </div>
    </div>
</body>
</html>
EOF

echo "✅ Created invitation email template"

echo ""
echo "🎯 NEXT STEPS TO FIX INVITATION SYSTEM:"
echo "====================================="
echo "1. Run diagnosis script first: ./diagnose-user-invitations.sh"
echo "2. Fix existing users with login issues: php fix_user_password.php user@email.com"
echo "3. Update your invitation method using the template in improved_invitation_method.txt"
echo "4. Test with a new invitation"
echo ""
echo "📋 Key fixes applied:"
echo "✅ Password helper script created"
echo "✅ Improved invitation method template"
echo "✅ Clear email template with exact password display"
echo "✅ Auto email verification for invited users"