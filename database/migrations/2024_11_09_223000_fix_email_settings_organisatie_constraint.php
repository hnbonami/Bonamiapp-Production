<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // De kolom organisatie_id bestaat al, foreign key en unique constraint ook
        // We hoeven alleen de data te migreren
        $this->migrateExistingData();
    }

    /**
     * Migreer bestaande email settings data
     */
    private function migrateExistingData()
    {
        // Check of er settings zijn zonder organisatie_id
        $settingsWithoutOrg = DB::table('email_settings')->whereNull('organisatie_id')->get();
        
        if ($settingsWithoutOrg->isNotEmpty()) {
            $organisaties = DB::table('organisaties')->get();
            
            foreach ($settingsWithoutOrg as $existingSettings) {
                foreach ($organisaties as $organisatie) {
                    // Check of deze organisatie al een settings record heeft
                    $hasSettings = DB::table('email_settings')
                                     ->where('organisatie_id', $organisatie->id)
                                     ->exists();
                    
                    if (!$hasSettings) {
                        // Maak een kopie van de settings voor deze organisatie
                        $settingsArray = (array) $existingSettings;
                        unset($settingsArray['id']); // Verwijder ID zodat nieuwe record wordt aangemaakt
                        $settingsArray['organisatie_id'] = $organisatie->id;
                        
                        DB::table('email_settings')->insert($settingsArray);
                        
                        \Log::info('✅ Email settings gemigreerd voor organisatie', [
                            'organisatie_id' => $organisatie->id,
                            'organisatie_naam' => $organisatie->naam
                        ]);
                    }
                }
                
                // Verwijder het oude record zonder organisatie_id
                DB::table('email_settings')->where('id', $existingSettings->id)->delete();
            }
            
            \Log::info('🔄 Email settings data migratie voltooid');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback niet nodig - we hebben alleen data gemigreerd
        \Log::warning('⚠️ Email settings data rollback wordt niet ondersteund');
    }
};
