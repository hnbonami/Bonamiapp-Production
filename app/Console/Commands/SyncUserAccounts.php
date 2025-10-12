<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Klant;
use App\Models\Medewerker;

class SyncUserAccounts extends Command
{
    protected $signature = 'users:sync-accounts';
    protected $description = 'Synchroniseer klanten en medewerkers met user accounts';

    public function handle()
    {
        $this->info('🔄 Bezig met synchroniseren van user accounts...');
        
        // Stap 1: Fix rol inconsistenties
        $this->fixRoleInconsistencies();
        
        // Stap 2: Maak missing user accounts aan
        $this->createMissingUserAccounts();
        
        // Stap 3: Sync bestaande accounts
        $this->syncExistingAccounts();
        
        // Stap 4: Clean up orphaned users
        $this->cleanupOrphanedUsers();
        
        $this->showUserStats();
        
        $this->info('✅ User account synchronisatie voltooid!');
    }

    private function fixRoleInconsistencies()
    {
        $this->info('🔧 Bezig met fixen van rol inconsistenties...');
        
        // Fix 'customer' naar 'klant'
        $customerUsers = User::where('role', 'customer')->get();
        foreach ($customerUsers as $user) {
            $user->update(['role' => 'klant']);
            $this->line("   User {$user->email}: rol gewijzigd van 'customer' naar 'klant'");
        }
        
        $this->info("   {$customerUsers->count()} gebruikers gefixed van 'customer' naar 'klant'");
    }

    private function createMissingUserAccounts()
    {
        $this->info('➕ Bezig met aanmaken van ontbrekende user accounts...');
        
        // Klanten zonder user account
        $klantenZonderUser = Klant::whereNull('user_id')
                                  ->orWhereDoesntHave('user')
                                  ->whereNotNull('email')
                                  ->get();
        
        foreach ($klantenZonderUser as $klant) {
            if (!User::where('email', $klant->email)->exists()) {
                $klant->createUserAccount();
                $this->line("   Klant user aangemaakt: {$klant->email}");
            } else {
                // Koppel bestaande user
                $existingUser = User::where('email', $klant->email)->first();
                $existingUser->update(['role' => 'klant']);
                $klant->update(['user_id' => $existingUser->id]);
                $this->line("   Klant gekoppeld aan bestaande user: {$klant->email}");
            }
        }
        
        // Medewerkers zonder user account
        $medewerkersZonderUser = Medewerker::whereNull('user_id')
                                           ->orWhereDoesntHave('user')
                                           ->whereNotNull('email')
                                           ->get();
        
        foreach ($medewerkersZonderUser as $medewerker) {
            if (!User::where('email', $medewerker->email)->exists()) {
                $medewerker->createUserAccount();
                $this->line("   Medewerker user aangemaakt: {$medewerker->email}");
            } else {
                // Koppel bestaande user
                $existingUser = User::where('email', $medewerker->email)->first();
                $existingUser->update(['role' => 'medewerker']);
                $medewerker->update(['user_id' => $existingUser->id]);
                $this->line("   Medewerker gekoppeld aan bestaande user: {$medewerker->email}");
            }
        }
        
        $this->info("   {$klantenZonderUser->count()} klanten en {$medewerkersZonderUser->count()} medewerkers verwerkt");
    }

    private function syncExistingAccounts()
    {
        $this->info('🔄 Bezig met synchroniseren van bestaande accounts...');
        
        // Sync klanten
        $klanten = Klant::whereNotNull('user_id')->with('user')->get();
        foreach ($klanten as $klant) {
            if ($klant->user) {
                $klant->syncToUserAccount();
            }
        }
        
        // Sync medewerkers
        $medewerkers = Medewerker::whereNotNull('user_id')->with('user')->get();
        foreach ($medewerkers as $medewerker) {
            if ($medewerker->user) {
                $medewerker->syncToUserAccount();
            }
        }
        
        $this->info("   {$klanten->count()} klanten en {$medewerkers->count()} medewerkers gesynchroniseerd");
    }

    private function cleanupOrphanedUsers()
    {
        $this->info('🧹 Bezig met opruimen van ontkoppelde users...');
        
        // Find users zonder klant/medewerker koppeling (behalve admins)
        $orphanedUsers = User::where('role', '!=', 'admin')
                            ->whereDoesntHave('klant')
                            ->whereDoesntHave('medewerker')
                            ->get();
        
        foreach ($orphanedUsers as $user) {
            // Probeer te koppelen op basis van email
            $klant = Klant::where('email', $user->email)->first();
            $medewerker = Medewerker::where('email', $user->email)->first();
            
            if ($klant) {
                $klant->update(['user_id' => $user->id]);
                $user->update(['role' => 'klant']);
                $this->line("   Orphaned user gekoppeld aan klant: {$user->email}");
            } elseif ($medewerker) {
                $medewerker->update(['user_id' => $user->id]);
                $user->update(['role' => 'medewerker']);
                $this->line("   Orphaned user gekoppeld aan medewerker: {$user->email}");
            } else {
                $this->warn("   Orphaned user gevonden zonder match: {$user->email} (rol: {$user->role})");
            }
        }
        
        $this->info("   {$orphanedUsers->count()} ontkoppelde users verwerkt");
    }

    private function showUserStats()
    {
        $this->info("\n📊 User Account Statistieken:");
        
        $stats = [
            'Admin users' => User::where('role', 'admin')->count(),
            'Klant users' => User::where('role', 'klant')->count(),
            'Medewerker users' => User::where('role', 'medewerker')->count(),
            'Andere rollen' => User::whereNotIn('role', ['admin', 'klant', 'medewerker'])->count(),
            'Totaal users' => User::count(),
            'Totaal klanten' => Klant::count(),
            'Totaal medewerkers' => Medewerker::count(),
            'Klanten met user' => Klant::whereNotNull('user_id')->count(),
            'Medewerkers met user' => Medewerker::whereNotNull('user_id')->count(),
        ];
        
        foreach ($stats as $label => $count) {
            $this->line("   {$label}: {$count}");
        }
    }
}