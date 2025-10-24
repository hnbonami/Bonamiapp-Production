<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update oude feature keys naar nieuwe naming convention (zonder underscores)
        $updates = [
            'klanten_beheer' => 'klantenbeheer',
            'medewerkers_beheer' => 'medewerkerbeheer',
            'testzadels_beheer' => 'testzadels',
            'pdf_generatie' => 'bikefits', // PDF generatie is onderdeel van bikefits
            'email_templates' => 'sjablonen', // Email templates zijn onderdeel van sjablonen
        ];

        foreach ($updates as $oldKey => $newKey) {
            // Check of de oude key bestaat
            $oldFeature = DB::table('features')->where('key', $oldKey)->first();
            $newFeature = DB::table('features')->where('key', $newKey)->first();
            
            if ($oldFeature) {
                if ($newFeature) {
                    // Nieuwe feature bestaat al, kopieer de relaties over
                    $this->command->info("Kopieer relaties van '{$oldKey}' naar '{$newKey}'");
                    
                    // Haal alle organisatie_features op voor de oude key
                    $relations = DB::table('organisatie_features')
                        ->where('feature_id', $oldFeature->id)
                        ->get();
                    
                    foreach ($relations as $relation) {
                        // Check of relatie al bestaat voor nieuwe feature
                        $exists = DB::table('organisatie_features')
                            ->where('organisatie_id', $relation->organisatie_id)
                            ->where('feature_id', $newFeature->id)
                            ->exists();
                        
                        if (!$exists) {
                            DB::table('organisatie_features')->insert([
                                'organisatie_id' => $relation->organisatie_id,
                                'feature_id' => $newFeature->id,
                                'is_actief' => $relation->is_actief,
                                'expires_at' => $relation->expires_at,
                                'notities' => $relation->notities,
                                'created_at' => $relation->created_at,
                                'updated_at' => now(),
                            ]);
                        }
                    }
                    
                    // Verwijder oude feature
                    DB::table('organisatie_features')->where('feature_id', $oldFeature->id)->delete();
                    DB::table('features')->where('id', $oldFeature->id)->delete();
                    
                    $this->command->info("✅ Oude feature '{$oldKey}' verwijderd en samengevoegd met '{$newKey}'");
                } else {
                    // Nieuwe feature bestaat niet, update de key
                    DB::table('features')
                        ->where('key', $oldKey)
                        ->update(['key' => $newKey]);
                    
                    $this->command->info("✅ Feature key '{$oldKey}' geüpdatet naar '{$newKey}'");
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback niet nodig, oude data is al verwijderd
        $this->command->warn('⚠️ Rollback niet beschikbaar - oude feature keys zijn samengevoegd');
    }
};