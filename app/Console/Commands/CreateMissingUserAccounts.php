<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Klant;
use App\Models\User;

class CreateMissingUserAccounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:create-missing {--dry-run : Toon wat er zou gebeuren zonder daadwerkelijk aan te maken}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Maak user accounts aan voor klanten die nog geen account hebben';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Zoek klanten zonder user account...');
        
        // Haal eerst alle user emails op
        $userEmails = User::pluck('email')->toArray();
        
        // Vind klanten waarvan het email NIET in de users tabel voorkomt
        $klantenZonderUser = Klant::whereNotIn('email', $userEmails)
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->get();
        
        $this->info("Gevonden: {$klantenZonderUser->count()} klanten zonder user account");
        
        if ($klantenZonderUser->count() === 0) {
            $this->info('✅ Alle klanten hebben al een user account!');
            return 0;
        }
        
        // Toon de klanten
        $this->table(
            ['ID', 'Naam', 'Email', 'Organisatie ID'],
            $klantenZonderUser->map(fn($k) => [
                $k->id,
                $k->naam,
                $k->email,
                $k->organisatie_id
            ])
        );
        
        // Check --dry-run flag
        if ($this->option('dry-run')) {
            $this->warn('🔸 DRY RUN - Geen accounts aangemaakt');
            return 0;
        }
        
        // Vraag bevestiging
        if (!$this->confirm('Wil je voor deze klanten user accounts aanmaken?')) {
            $this->warn('⏹️  Geannuleerd');
            return 0;
        }
        
        $this->info('');
        $this->info('📝 User accounts aanmaken...');
        
        $created = 0;
        $skipped = 0;
        $errors = 0;
        
        $progressBar = $this->output->createProgressBar($klantenZonderUser->count());
        
        foreach ($klantenZonderUser as $klant) {
            try {
                $user = User::create([
                    'name' => $klant->naam,
                    'email' => $klant->email,
                    'password' => \Hash::make(\Str::random(16)), // Random wachtwoord
                    'role' => 'klant',
                    'organisatie_id' => $klant->organisatie_id,
                    'status' => 'active',
                    'email_verified_at' => null, // Klant moet email verifiëren
                ]);
                
                $this->newLine();
                $this->line("✅ User aangemaakt: {$klant->naam} ({$klant->email})");
                $created++;
                
            } catch (\Exception $e) {
                $this->newLine();
                $this->error("❌ Fout bij {$klant->email}: {$e->getMessage()}");
                $errors++;
            }
            
            $progressBar->advance();
        }
        
        $progressBar->finish();
        $this->newLine(2);
        
        // Samenvatting
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('📊 Samenvatting:');
        $this->info("   ✅ Aangemaakt: {$created}");
        if ($skipped > 0) {
            $this->warn("   ⏭️  Overgeslagen: {$skipped}");
        }
        if ($errors > 0) {
            $this->error("   ❌ Fouten: {$errors}");
        }
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        
        return 0;
    }
}
