<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class SyncUsersWithKlantenMedewerkers extends Command
{
    protected $signature = 'sync:users';
    protected $description = 'Sync users table with klanten and medewerkers tables';

    public function handle()
    {
        $this->info('Starting user synchronization...');

        // Check current counts
        $klantenCount = DB::table('klanten')->count();
        $medewerkersCount = DB::table('medewerkers')->count();
        $usersCount = DB::table('users')->count();

        $this->info("Current counts:");
        $this->info("- Klanten: {$klantenCount}");
        $this->info("- Medewerkers: {$medewerkersCount}");
        $this->info("- Users: {$usersCount}");

        $this->info('Creating users for klanten...');
        $this->createUsersForKlanten();

        $this->info('Creating users for medewerkers...');
        $this->createUsersForMedewerkers();

        $this->info('Linking klanten to users...');
        $this->linkKlantenToUsers();

        $this->info('Linking medewerkers to users...');
        $this->linkMedewerkersToUsers();

        // Final counts
        $finalCounts = DB::select("
            SELECT 
                (SELECT COUNT(*) FROM users WHERE role = 'klant') as klant_users,
                (SELECT COUNT(*) FROM users WHERE role = 'medewerker') as medewerker_users,
                (SELECT COUNT(*) FROM klanten) as total_klanten,
                (SELECT COUNT(*) FROM medewerkers) as total_medewerkers
        ")[0];

        $this->info('Final counts:');
        $this->info("- Klant users: {$finalCounts->klant_users}");
        $this->info("- Medewerker users: {$finalCounts->medewerker_users}");
        $this->info("- Total klanten: {$finalCounts->total_klanten}");
        $this->info("- Total medewerkers: {$finalCounts->total_medewerkers}");

        $this->info('Synchronization completed!');
    }

    private function createUsersForKlanten()
    {
        $klanten = DB::table('klanten')->get();
        
        foreach ($klanten as $klant) {
            $email = $klant->email ?: "klant{$klant->id}@bonami.local";
            $name = trim(($klant->voornaam ?? '') . ' ' . ($klant->naam ?? ''));
            
            if (empty($name)) {
                $name = "Klant {$klant->id}";
            }

            User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => Hash::make('password123'),
                    'role' => 'klant',
                    'status' => 'active',
                    'email_verified_at' => now(),
                ]
            );
        }
    }

    private function createUsersForMedewerkers()
    {
        $medewerkers = DB::table('medewerkers')->get();
        
        foreach ($medewerkers as $medewerker) {
            $email = $medewerker->email ?: "medewerker{$medewerker->id}@bonami.local";
            $name = trim(($medewerker->voornaam ?? '') . ' ' . ($medewerker->naam ?? ''));
            
            if (empty($name)) {
                $name = "Medewerker {$medewerker->id}";
            }

            User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => Hash::make('password123'),
                    'role' => 'medewerker',
                    'status' => 'active',
                    'email_verified_at' => now(),
                ]
            );
        }
    }

    private function linkKlantenToUsers()
    {
        DB::statement("
            UPDATE klanten k 
            INNER JOIN users u ON (u.email = COALESCE(k.email, CONCAT('klant', k.id, '@bonami.local')))
            SET k.user_id = u.id
        ");
    }

    private function linkMedewerkersToUsers()
    {
        DB::statement("
            UPDATE medewerkers m 
            INNER JOIN users u ON (u.email = COALESCE(m.email, CONCAT('medewerker', m.id, '@bonami.local')))
            SET m.user_id = u.id
        ");
    }
}