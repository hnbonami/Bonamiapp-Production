<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

// Maak admin user aan
User::create([
    'name' => 'Admin Bonami',
    'email' => 'admin@bonami-sportcoaching.be',
    'password' => Hash::make('AdminBonami2024!'),
    'email_verified_at' => now(),
    'is_admin' => true, // Als je een admin kolom hebt
]);

echo "✅ Admin user succesvol aangemaakt!\n";
echo "Email: admin@bonami-sportcoaching.be\n";
echo "Wachtwoord: AdminBonami2024!\n";