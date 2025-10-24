<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Feature;

class CleanupDuplicateFeatures extends Command
{
    protected $signature = 'features:cleanup-duplicates';
    protected $description = 'Ruim duplicate feature keys op en migreer relaties';

    public function handle(): int
    {
        $this->info('🧹 Start cleanup van duplicate features...');
        
        DB::beginTransaction();
        
        try {
            // Mapping: oude feature ID => nieuwe feature ID
            $mapping = [
                4 => 14,  // klanten_beheer => klantenbeheer
                5 => 15,  // medewerkers_beheer => medewerkerbeheer
                6 => 16,  // testzadels_beheer => testzadels
                8 => 1,   // pdf_generatie => bikefits (PDF is onderdeel van bikefits)
                9 => 7,   // email_templates => sjablonen (Email templates zijn onderdeel van sjablonen)
            ];
            
            $this->info('');
            $this->info('📊 Mapping overzicht:');
            foreach ($mapping as $oldId => $newId) {
                $oldFeature = Feature::find($oldId);
                $newFeature = Feature::find($newId);
                $this->line("  {$oldId}: {$oldFeature->key} → {$newId}: {$newFeature->key}");
            }
            
            $this->info('');
            $this->info('🔄 Migreer organisatie relaties...');
            
            foreach ($mapping as $oldFeatureId => $newFeatureId) {
                // Haal alle organisaties op die de oude feature hebben
                $oldRelations = DB::table('organisatie_features')
                    ->where('feature_id', $oldFeatureId)
                    ->where('is_actief', 1) // Alleen actieve features
                    ->get();
                
                if ($oldRelations->count() > 0) {
                    $this->info("  Migreer {$oldRelations->count()} actieve relatie(s) van feature {$oldFeatureId} naar {$newFeatureId}");
                    
                    foreach ($oldRelations as $relation) {
                        // Check of de nieuwe feature relatie al bestaat
                        $exists = DB::table('organisatie_features')
                            ->where('organisatie_id', $relation->organisatie_id)
                            ->where('feature_id', $newFeatureId)
                            ->exists();
                        
                        if (!$exists) {
                            // Maak nieuwe relatie aan
                            DB::table('organisatie_features')->insert([
                                'organisatie_id' => $relation->organisatie_id,
                                'feature_id' => $newFeatureId,
                                'is_actief' => $relation->is_actief,
                                'expires_at' => $relation->expires_at,
                                'notities' => "Gemigreerd van feature {$oldFeatureId}",
                                'created_at' => $relation->created_at,
                                'updated_at' => now(),
                            ]);
                            
                            $this->line("    ✅ Organisatie {$relation->organisatie_id}: Nieuwe relatie aangemaakt");
                        } else {
                            $this->line("    ⏭️  Organisatie {$relation->organisatie_id}: Relatie bestaat al");
                        }
                    }
                }
                
                // Verwijder ALLE oude relaties (actief en niet-actief)
                $deletedCount = DB::table('organisatie_features')
                    ->where('feature_id', $oldFeatureId)
                    ->delete();
                
                $this->line("    🗑️  {$deletedCount} oude relatie(s) verwijderd");
            }
            
            $this->info('');
            $this->info('🗑️  Verwijder oude features...');
            
            foreach (array_keys($mapping) as $oldFeatureId) {
                $feature = Feature::find($oldFeatureId);
                if ($feature) {
                    $this->line("  Verwijder feature {$oldFeatureId}: {$feature->key}");
                    $feature->delete();
                }
            }
            
            DB::commit();
            
            $this->info('');
            $this->newLine();
            $this->info('✅ Cleanup succesvol voltooid!');
            $this->info('');
            $this->info('📋 Samenvatting:');
            $this->line('  - Actieve organisatie relaties gemigreerd naar nieuwe features');
            $this->line('  - Niet-actieve relaties verwijderd');
            $this->line('  - ' . count($mapping) . ' oude features verwijderd');
            $this->info('');
            $this->info('🎯 Volgende stap:');
            $this->line('  Run: php artisan organisatie:check-features 2');
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('');
            $this->error('❌ Error tijdens cleanup: ' . $e->getMessage());
            $this->error('');
            $this->error('Database rollback uitgevoerd - geen wijzigingen opgeslagen');
            
            return Command::FAILURE;
        }
    }
}