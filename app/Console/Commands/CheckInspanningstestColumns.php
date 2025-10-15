<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CheckInspanningstestColumns extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:check-inspanningstest';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Controleer welke kolommen bestaan in inspanningstests tabel';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Controleren inspanningstests tabel structuur...');
        $this->newLine();
        
        if (!Schema::hasTable('inspanningstests')) {
            $this->error('❌ Tabel "inspanningstests" bestaat niet!');
            return 1;
        }
        
        // Haal alle kolommen op
        $columns = Schema::getColumnListing('inspanningstests');
        
        $this->info('✅ Tabel "inspanningstests" bestaat met ' . count($columns) . ' kolommen:');
        $this->newLine();
        
        // Toon kolommen in een mooie tabel
        $tableData = [];
        foreach ($columns as $column) {
            // Haal kolom details op
            $columnType = DB::select("SHOW COLUMNS FROM inspanningstests WHERE Field = ?", [$column])[0];
            
            $tableData[] = [
                'Kolom' => $column,
                'Type' => $columnType->Type,
                'Null' => $columnType->Null,
                'Default' => $columnType->Default ?? 'NULL',
            ];
        }
        
        $this->table(
            ['Kolom', 'Type', 'Null', 'Default'],
            $tableData
        );
        
        $this->newLine();
        
        // Check welke kolommen ontbreken die we willen toevoegen
        $gewensteKolommen = [
            'user_id',
            'aerobe_drempel_vermogen',
            'aerobe_drempel_hartslag',
            'anaerobe_drempel_vermogen',
            'anaerobe_drempel_hartslag',
            'lichaamsgewicht_kg',
            'lichaamslengte_cm',
            'bmi',
            'vetpercentage',
            'buikomtrek_cm',
            'hartslag_rust_bpm',
            'maximale_hartslag_bpm',
            'slaapkwaliteit',
            'eetlust',
            'gevoel_op_training',
            'stressniveau',
            'gemiddelde_trainingstatus',
            'training_dag_voor_test',
            'training_2d_voor_test',
            'startwattage',
            'stappen_min',
            'stappen_watt',
        ];
        
        $ontbrekendeKolommen = array_diff($gewensteKolommen, $columns);
        $bestaandeGewensteKolommen = array_intersect($gewensteKolommen, $columns);
        
        if (count($bestaandeGewensteKolommen) > 0) {
            $this->info('✅ Deze gewenste kolommen bestaan AL:');
            foreach ($bestaandeGewensteKolommen as $kolom) {
                $this->line('   • ' . $kolom);
            }
            $this->newLine();
        }
        
        if (count($ontbrekendeKolommen) > 0) {
            $this->warn('⚠️  Deze gewenste kolommen ONTBREKEN:');
            foreach ($ontbrekendeKolommen as $kolom) {
                $this->line('   • ' . $kolom);
            }
        } else {
            $this->info('🎉 Alle gewenste kolommen bestaan al!');
        }
        
        return 0;
    }
}
