<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Prestatie;
use App\Models\User;
use App\Models\Organisatie;

class FixPrestatiesOrganisatieId extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:prestaties-organisatie';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix prestaties zonder organisatie_id door user organisatie te gebruiken';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 Prestaties Organisatie ID Fix Script');
        $this->info('==========================================');
        
        // Check hoeveel prestaties organisatie_id NULL hebben
        $nullCount = Prestatie::whereNull('organisatie_id')->count();
        $this->warn("❌ Prestaties zonder organisatie_id: {$nullCount}");
        
        if ($nullCount === 0) {
            $this->info('✅ Alle prestaties hebben al een organisatie_id!');
            return 0;
        }
        
        $this->newLine();
        $this->info('🔄 Prestaties updaten...');
        
        $bar = $this->output->createProgressBar($nullCount);
        $bar->start();
        
        $updated = 0;
        $errors = 0;
        
        // Fix: Update alle prestaties zonder organisatie_id
        Prestatie::whereNull('organisatie_id')->chunk(100, function($prestaties) use (&$updated, &$errors, $bar) {
            foreach ($prestaties as $prestatie) {
                $user = User::find($prestatie->user_id);
                if ($user && $user->organisatie_id) {
                    $prestatie->update(['organisatie_id' => $user->organisatie_id]);
                    $updated++;
                } else {
                    $this->newLine();
                    $this->warn("⚠️  Prestatie {$prestatie->id} - user niet gevonden of geen organisatie_id");
                    $errors++;
                }
                $bar->advance();
            }
        });
        
        $bar->finish();
        
        $this->newLine(2);
        $this->info('📊 RESULTAAT:');
        $this->info('==========================================');
        $this->info("Totaal prestaties: " . Prestatie::count());
        $this->info("✅ Succesvol updated: {$updated}");
        if ($errors > 0) {
            $this->warn("⚠️  Errors: {$errors}");
        }
        $this->info("Prestaties zonder organisatie_id: " . Prestatie::whereNull('organisatie_id')->count());
        
        $this->newLine();
        $this->info('Prestaties per organisatie:');
        
        Prestatie::selectRaw('organisatie_id, COUNT(*) as count')
            ->groupBy('organisatie_id')
            ->get()
            ->each(function($row) {
                if ($row->organisatie_id) {
                    $org = Organisatie::find($row->organisatie_id);
                    $this->info("  📊 Organisatie {$row->organisatie_id} ({$org->naam}): {$row->count} prestaties");
                } else {
                    $this->warn("  ❌ NULL: {$row->count} prestaties");
                }
            });
        
        $this->newLine();
        $this->info('✅ KLAAR!');
        
        return 0;
    }
}