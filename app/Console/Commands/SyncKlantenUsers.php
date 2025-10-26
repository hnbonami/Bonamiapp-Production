<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Klant;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SyncKlantenUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'klanten:sync-users 
                            {--dry-run : Toon wat er zou gebeuren zonder wijzigingen door te voeren}
                            {--force : Voer sync uit zonder bevestiging}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchroniseer klanten met user accounts - maak missende user accounts aan';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');
        
        $this->info('🔍 Zoeken naar klanten zonder user account...');
        
        // Haal alle organisaties op
        $organisaties = \App\Models\Organisatie::all();
        
        foreach ($organisaties as $organisatie) {
            $this->newLine();
            $this->info("📊 Organisatie: {$organisatie->naam} (ID: {$organisatie->id})");
            
            // Tel klanten en users voor deze organisatie
            $totalKlanten = Klant::where('organisatie_id', $organisatie->id)->count();
            $totalKlantUsers = User::where('organisatie_id', $organisatie->id)
                ->where('role', 'klant')
                ->count();
            
            $this->table(
                ['Metric', 'Aantal'],
                [
                    ['Totaal klanten', $totalKlanten],
                    ['Klant users', $totalKlantUsers],
                    ['Verschil', $totalKlanten - $totalKlantUsers]
                ]
            );
            
            // Zoek klanten zonder user account
            $klantenZonderUser = Klant::where('organisatie_id', $organisatie->id)
                ->whereNotIn('email', function($query) {
                    $query->select('email')
                          ->from('users');
                })
                ->get();
            
            if ($klantenZonderUser->count() === 0) {
                $this->info("✅ Alle klanten hebben een user account!");
                continue;
            }
            
            $this->warn("⚠️  Gevonden: {$klantenZonderUser->count()} klanten zonder user account");
            
            // Toon eerste 10 voor overzicht
            $this->table(
                ['ID', 'Naam', 'Email', 'Aangemaakt op'],
                $klantenZonderUser->take(10)->map(fn($k) => [
                    $k->id,
                    $k->naam,
                    $k->email,
                    $k->created_at->format('d-m-Y H:i')
                ])
            );
            
            if ($klantenZonderUser->count() > 10) {
                $this->info("... en nog " . ($klantenZonderUser->count() - 10) . " meer");
            }
            
            // Check voor dry-run
            if ($dryRun) {
                $this->info("🔍 DRY RUN: Zou {$klantenZonderUser->count()} user accounts aanmaken");
                continue;
            }
            
            // Vraag bevestiging (tenzij --force)
            if (!$force) {
                if (!$this->confirm("Wil je {$klantenZonderUser->count()} user accounts aanmaken voor deze klanten?", false)) {
                    $this->info("❌ Overgeslagen voor {$organisatie->naam}");
                    continue;
                }
            }
            
            // Maak user accounts aan
            $this->info("🔧 User accounts aanmaken...");
            $progressBar = $this->output->createProgressBar($klantenZonderUser->count());
            $progressBar->start();
            
            $created = 0;
            $errors = [];
            
            foreach ($klantenZonderUser as $klant) {
                try {
                    // Check nogmaals of er geen user bestaat (race condition)
                    if (User::where('email', $klant->email)->exists()) {
                        $progressBar->advance();
                        continue;
                    }
                    
                    // Maak user account aan
                    $user = User::create([
                        'name' => $klant->naam,
                        'email' => $klant->email,
                        'password' => Hash::make('changeme123'), // Default wachtwoord
                        'role' => 'klant',
                        'organisatie_id' => $klant->organisatie_id,
                        'status' => 'active',
                        'email_verified_at' => now(), // Auto-verify
                    ]);
                    
                    $created++;
                    
                    \Log::info('✅ User account aangemaakt voor klant', [
                        'klant_id' => $klant->id,
                        'user_id' => $user->id,
                        'email' => $klant->email,
                        'organisatie_id' => $klant->organisatie_id
                    ]);
                    
                } catch (\Exception $e) {
                    $errors[] = "Klant {$klant->id} ({$klant->naam}): {$e->getMessage()}";
                    \Log::error('❌ User account aanmaken gefaald', [
                        'klant_id' => $klant->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
                
                $progressBar->advance();
            }
            
            $progressBar->finish();
            $this->newLine(2);
            
            // Resultaten voor deze organisatie
            $this->info("✅ Sync voltooid voor {$organisatie->naam}!");
            $this->info("📊 Aangemaakt: {$created} van {$klantenZonderUser->count()} user accounts");
            
            if (count($errors) > 0) {
                $this->error("❌ Fouten: " . count($errors));
                foreach ($errors as $error) {
                    $this->error("  - {$error}");
                }
            }
        }
        
        $this->newLine();
        $this->info('🎉 Alle organisaties verwerkt!');
        $this->info('💡 Tip: Klanten moeten hun wachtwoord resetten (default: "changeme123")');
        
        return 0;
    }
}
