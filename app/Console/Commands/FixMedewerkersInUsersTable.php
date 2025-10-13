<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class FixMedewerkersInUsersTable extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'bonami:fix-medewerkers';

    /**
     * The console command description.
     */
    protected $description = 'Fix medewerkers in users table - voeg ontbrekende kolommen toe en werk bestaande medewerkers bij';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔧 Fixing medewerkers in users table...');

        try {
            // Stap 1: Voeg ontbrekende kolommen toe aan users tabel
            $this->info('📋 Checking and adding missing columns to users table...');
            
            if (!Schema::hasColumn('users', 'voornaam')) {
                DB::statement('ALTER TABLE users ADD COLUMN voornaam VARCHAR(255) NULL AFTER name');
                $this->info('✅ Added voornaam column');
            }
            
            if (!Schema::hasColumn('users', 'achternaam')) {
                DB::statement('ALTER TABLE users ADD COLUMN achternaam VARCHAR(255) NULL AFTER voornaam');
                $this->info('✅ Added achternaam column');
            }
            
            if (!Schema::hasColumn('users', 'telefoon')) {
                DB::statement('ALTER TABLE users ADD COLUMN telefoon VARCHAR(20) NULL AFTER email');
                $this->info('✅ Added telefoon column');
            }

            // Stap 2: Update bestaande medewerkers zonder voornaam/achternaam
            $this->info('👥 Updating existing medewerkers...');
            
            $medewerkersToUpdate = User::where('role', '!=', 'klant')
                                      ->where(function($query) {
                                          $query->whereNull('voornaam')
                                                ->orWhereNull('achternaam');
                                      })
                                      ->get();

            $updated = 0;
            foreach ($medewerkersToUpdate as $medewerker) {
                // Probeer naam te splitsen
                $nameParts = explode(' ', $medewerker->name, 2);
                $voornaam = $nameParts[0] ?? 'Onbekend';
                $achternaam = $nameParts[1] ?? 'Onbekend';
                
                $medewerker->update([
                    'voornaam' => $voornaam,
                    'achternaam' => $achternaam
                ]);
                
                $updated++;
                $this->line("  Updated: {$medewerker->name} -> {$voornaam} {$achternaam}");
            }

            // Stap 3: Controleer resultaat
            $totalMedewerkers = User::where('role', '!=', 'klant')->count();
            $medewerkersWithDetails = User::where('role', '!=', 'klant')
                                         ->whereNotNull('voornaam')
                                         ->whereNotNull('achternaam')
                                         ->count();

            $this->info("📊 Results:");
            $this->line("  Total medewerkers: {$totalMedewerkers}");
            $this->line("  Medewerkers with details: {$medewerkersWithDetails}");
            $this->line("  Updated this run: {$updated}");

            if ($totalMedewerkers === $medewerkersWithDetails) {
                $this->info('🎉 All medewerkers now have complete details!');
                $this->info('✅ Medewerkers should now appear correctly in /users list');
            } else {
                $this->warn('⚠️ Some medewerkers still missing details - check manually');
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Error fixing medewerkers: ' . $e->getMessage());
            $this->error('Trace: ' . $e->getTraceAsString());
            return Command::FAILURE;
        }
    }
}