<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use App\Models\TemplateKey;
use App\Models\Bikefit;

class CheckBikefitFieldMapping extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'bonami:check-bikefit-fields';

    /**
     * The console command description.
     */
    protected $description = 'Check of bikefit template keys overeenkomen met database velden';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔍 Checking bikefit field mapping...');

        try {
            // Haal bikefit database kolommen op
            $bikefitColumns = Schema::getColumnListing('bikefits');
            $this->info('📊 Bikefit database kolommen (' . count($bikefitColumns) . '):');
            foreach ($bikefitColumns as $column) {
                $this->line("  - {$column}");
            }

            $this->info('');
            
            // Haal bikefit template keys op
            $bikefitTemplateKeys = TemplateKey::where('category', 'bikefit')->get();
            $this->info('🔑 Bikefit template keys (' . count($bikefitTemplateKeys) . '):');
            
            $missingFields = [];
            $matchingFields = [];
            
            foreach ($bikefitTemplateKeys as $templateKey) {
                // Extract field name from {{bikefit.field_name}}
                $fieldName = str_replace(['{{bikefit.', '}}'], '', $templateKey->key);
                
                if (in_array($fieldName, $bikefitColumns)) {
                    $matchingFields[] = $fieldName;
                    $this->info("  ✅ {$templateKey->key} → {$fieldName} (EXISTS)");
                } else {
                    $missingFields[] = $fieldName;
                    $this->error("  ❌ {$templateKey->key} → {$fieldName} (MISSING)");
                }
            }
            
            $this->info('');
            $this->info('📈 Samenvatting:');
            $this->info("  ✅ Matchende velden: " . count($matchingFields));
            $this->error("  ❌ Ontbrekende velden: " . count($missingFields));
            
            if (!empty($missingFields)) {
                $this->info('');
                $this->error('🚨 Ontbrekende database velden:');
                foreach ($missingFields as $field) {
                    $this->line("  - {$field}");
                }
                
                $this->info('');
                $this->info('💡 Mogelijke oplossingen:');
                $this->line('1. Voeg ontbrekende kolommen toe aan bikefits tabel met migration');
                $this->line('2. Of pas template key namen aan naar bestaande kolommen');
            }
            
            // Test met een echte bikefit record
            $this->info('');
            $this->info('🧪 Test met echte bikefit data...');
            $bikefit = Bikefit::first();
            
            if ($bikefit) {
                $this->info("Test bikefit ID: {$bikefit->id}");
                
                // Test enkele nieuwe velden
                $testFields = ['aanpassingen_zadel', 'aanpassingen_stuurpen_pre', 'rotatie_aanpassingen'];
                
                foreach ($testFields as $field) {
                    if (isset($bikefit->$field)) {
                        $value = $bikefit->$field ?? 'NULL';
                        $this->info("  ✅ {$field}: '{$value}'");
                    } else {
                        $this->warn("  ⚠️ {$field}: NIET BESCHIKBAAR");
                    }
                }
            } else {
                $this->warn('Geen bikefit records gevonden voor testen');
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}