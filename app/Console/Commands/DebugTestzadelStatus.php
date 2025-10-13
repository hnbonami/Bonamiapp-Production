<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Testzadel;
use Illuminate\Support\Facades\DB;

class DebugTestzadelStatus extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'bonami:debug-testzadel-status';

    /**
     * The console command description.
     */
    protected $description = 'Debug testzadel status inconsistenties tussen opslag en weergave';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔍 Debugging testzadel status inconsistenties...');

        try {
            // Haal alle testzadels op
            $testzadels = Testzadel::with('klant')->orderBy('created_at', 'desc')->get();

            $this->info('📋 Alle testzadels en hun status:');
            $this->table(
                ['ID', 'Klant', 'Status (DB)', 'Onderdeel Type', 'Uitleen Datum', 'Verwachte Retour', 'Created At'],
                $testzadels->map(function($testzadel) {
                    return [
                        $testzadel->id,
                        $testzadel->klant ? $testzadel->klant->naam : 'Geen klant',
                        $testzadel->status ?? 'NULL',
                        $testzadel->onderdeel_type ?? 'NULL', 
                        $testzadel->uitleen_datum ?? 'NULL',
                        $testzadel->verwachte_retour_datum ?? 'NULL',
                        $testzadel->created_at->format('Y-m-d H:i:s')
                    ];
                })->toArray()
            );

            // Controleer de database kolommen
            $this->info('');
            $this->info('🗃️ Database kolom informatie voor testzadels tabel:');
            $columns = DB::select("DESCRIBE testzadels");
            
            foreach($columns as $column) {
                if($column->Field === 'status') {
                    $this->line("Status kolom: {$column->Field} | Type: {$column->Type} | Null: {$column->Null} | Default: {$column->Default}");
                }
            }

            // Zoek naar recente testzadels die mogelijk het probleem hebben
            $this->info('');
            $this->info('🔍 Recente testzadels (laatste 5):');
            $recenteTestzadels = Testzadel::with('klant')
                                         ->orderBy('created_at', 'desc')
                                         ->limit(5)
                                         ->get();

            foreach($recenteTestzadels as $testzadel) {
                $this->line("ID {$testzadel->id}: Status='{$testzadel->status}', Type='{$testzadel->onderdeel_type}', Klant=" . ($testzadel->klant ? $testzadel->klant->naam : 'Geen'));
                
                // Log alle attributen voor debugging
                \Log::info('Testzadel details voor debugging', [
                    'id' => $testzadel->id,
                    'status' => $testzadel->status,
                    'onderdeel_type' => $testzadel->onderdeel_type,
                    'raw_attributes' => $testzadel->getAttributes()
                ]);
            }

            // Controleer of er mogelijk meerdere statussen zijn
            $this->info('');
            $this->info('📊 Unieke status waarden in database:');
            $uniqueStatuses = Testzadel::select('status')
                                      ->distinct()
                                      ->whereNotNull('status')
                                      ->pluck('status')
                                      ->toArray();
            
            foreach($uniqueStatuses as $status) {
                $count = Testzadel::where('status', $status)->count();
                $this->line("'{$status}' => {$count} testzadels");
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Error debugging testzadel status: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return Command::FAILURE;
        }
    }
}