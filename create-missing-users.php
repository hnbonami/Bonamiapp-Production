<?php

// Simple script to create missing user accounts
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$orgId = 1;

// Get klanten with user emails
$klantenMetUser = \App\Models\User::where('organisatie_id', $orgId)
    ->where('role', 'klant')
    ->pluck('email')
    ->toArray();

// Find klanten without users
$klantenZonderUser = \App\Models\Klant::where('organisatie_id', $orgId)
    ->whereNotIn('email', $klantenMetUser)
    ->whereNotNull('email')
    ->where('email', '!=', '')
    ->get();

echo "Gevonden: " . $klantenZonderUser->count() . " klanten zonder user account\n\n";

$created = 0;
foreach ($klantenZonderUser as $klant) {
    try {
        $user = \App\Models\User::create([
            'name' => $klant->naam,
            'email' => $klant->email,
            'password' => \Hash::make(\Str::random(16)),
            'role' => 'klant',
            'organisatie_id' => $klant->organisatie_id,
            'status' => 'active',
            'email_verified_at' => null,
        ]);
        
        echo "✅ User aangemaakt: {$klant->naam} ({$klant->email})\n";
        $created++;
        
    } catch (\Exception $e) {
        echo "❌ Fout bij {$klant->email}: {$e->getMessage()}\n";
    }
}

echo "\n✅ Klaar! {$created} user accounts aangemaakt.\n";
