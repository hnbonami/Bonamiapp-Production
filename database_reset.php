<?php

require_once __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Klant;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

echo "🔄 Database wordt gereset...\n";

// Truncate tabellen
DB::statement('SET FOREIGN_KEY_CHECKS=0;');
User::truncate();
Klant::truncate();
DB::statement('SET FOREIGN_KEY_CHECKS=1;');

echo "✅ Tabellen geleegd\n";

// Maak admin user
$admin = User::create([
    'name' => 'Admin Bonami',
    'email' => 'admin@bonami.be',
    'email_verified_at' => now(),
    'password' => Hash::make('admin123'),
    'user_type' => 'admin',
    'klant_id' => null
]);

echo "✅ Admin aangemaakt: admin@bonami.be / admin123\n";

// Maak voorbeeld klant
$klant = Klant::create([
    'naam' => 'Jan Janssens',
    'email' => 'jan@example.com',
    'telefoon' => '0123456789',
    'geboortedatum' => '1990-01-01',
    'geslacht' => 'M',
    'lengte_cm' => 180,
    'gewicht_kg' => 75.5,
    'beroep' => 'Software Developer',
    'sportervaring' => 'Recreatief fietsen, 3x per week'
]);

$klantUser = User::create([
    'name' => 'Jan Janssens',
    'email' => 'jan@example.com',
    'email_verified_at' => now(),
    'password' => Hash::make('password'),
    'user_type' => 'klant',
    'klant_id' => $klant->id
]);

echo "✅ Klant aangemaakt: jan@example.com / password\n";
echo "🎉 Database reset voltooid!\n";
echo "\nLogin gegevens:\n";
echo "Admin: admin@bonami.be / admin123\n";
echo "Klant: jan@example.com / password\n";