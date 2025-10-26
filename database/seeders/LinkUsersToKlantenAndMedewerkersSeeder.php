<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Klant;
use App\Models\Medewerker;
use Illuminate\Support\Facades\Hash;

class LinkUsersToKlantenAndMedewerkersSeeder extends Seeder
{
    public function run()
    {
        echo "🔗 Linking existing Klanten and Medewerkers to Users...\n\n";

        // Link existing Klanten to Users
        $klanten = Klant::whereNull('user_id')->get();
        
        foreach ($klanten as $klant) {
            // Try to find existing user by email
            $user = User::where('email', $klant->email)->first();
            
            if (!$user && $klant->email) {
                // Create new user for klant
                $user = User::create([
                    'name' => trim($klant->voornaam . ' ' . $klant->naam),
                    'email' => $klant->email,
                    'password' => Hash::make('password'), // Default password
                    'role' => 'klant',
                    'status' => 'active',
                    'email_verified_at' => now(),
                ]);
                
                echo "✅ Created user for klant: {$user->name} ({$user->email})\n";
            }
            
            if ($user) {
                // Link klant to user
                $klant->update(['user_id' => $user->id]);
                
                // Update user role if needed
                // Check of deze user een staff member is (niet klant)
                // Medewerkers en stagiairs worden behandeld als staff
                if ($user->role !== 'klant') {
                    $user->update(['role' => 'klant']);
                }
                
                echo "🔗 Linked klant {$klant->voornaam} {$klant->naam} to user {$user->email}\n";
            }
        }

        echo "\n";

        // Link existing Medewerkers to Users
        $medewerkers = Medewerker::whereNull('user_id')->get();
        
        foreach ($medewerkers as $medewerker) {
            // Try to find existing user by email
            $user = User::where('email', $medewerker->email)->first();
            
            if (!$user && $medewerker->email) {
                // Create new user for medewerker
                $user = User::create([
                    'name' => trim($medewerker->voornaam . ' ' . $medewerker->naam),
                    'email' => $medewerker->email,
                    'password' => Hash::make('password'), // Default password
                    'role' => 'medewerker',
                    'status' => 'active',
                    'email_verified_at' => now(),
                ]);
                
                echo "✅ Created user for medewerker: {$user->name} ({$user->email})\n";
            }
            
            if ($user) {
                // Link medewerker to user
                $medewerker->update(['user_id' => $user->id]);
                
                // Update user role if needed
                if (!in_array($user->role, ['admin', 'medewerker'])) {
                    $user->update(['role' => 'medewerker']);
                }
                
                echo "🔗 Linked medewerker {$medewerker->voornaam} {$medewerker->naam} to user {$user->email}\n";
            }
        }

        // Update user counts for display
        $totalUsers = User::count();
        $adminCount = User::where('role', 'admin')->count();
        $medewerkerCount = User::where('role', 'medewerker')->count();
        $klantCount = User::where('role', 'klant')->count();

        echo "\n📊 FINAL STATS:\n";
        echo "Total Users: {$totalUsers}\n";
        echo "Admins: {$adminCount}\n";
        echo "Medewerkers: {$medewerkerCount}\n";
        echo "Klanten: {$klantCount}\n";
        echo "\n✅ All done! Users are now properly linked.\n";
    }
}