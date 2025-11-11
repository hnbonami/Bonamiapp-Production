<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AnalyzeSjablonen extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'analyze:sjablonen';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Analyseer sjablonen database structuur en data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== 🔍 SJABLONEN DATABASE ANALYSE ===');
        $this->newLine();

        // 1. Check database structuur
        $this->info('📊 DATABASE STRUCTUUR');
        $this->line(str_repeat('-', 60));
        $columns = Schema::getColumnListing('sjablonen');
        $this->line('Kolommen in sjablonen tabel:');
        foreach ($columns as $column) {
            $type = DB::select("SHOW COLUMNS FROM sjablonen WHERE Field = ?", [$column])[0]->Type;
            $this->line("  - {$column} ({$type})");
        }

        // 2. Alle sjablonen ophalen
        $this->newLine();
        $this->info('📋 ALLE SJABLONEN IN DATABASE');
        $this->line(str_repeat('-', 60));
        $sjablonen = DB::table('sjablonen')
            ->select('id', 'naam', 'categorie', 'testtype', 'organisatie_id', 'is_actief')
            ->orderBy('organisatie_id')
            ->orderBy('naam')
            ->get();

        $this->line("Totaal aantal sjablonen: {$sjablonen->count()}");
        $this->newLine();

        $groepeerd = $sjablonen->groupBy('organisatie_id');
        foreach ($groepeerd as $orgId => $orgSjablonen) {
            $org = DB::table('organisaties')->where('id', $orgId)->first();
            $orgNaam = $org ? $org->naam : 'Onbekend';
            
            $this->line("🏢 ORGANISATIE {$orgId} - {$orgNaam} ({$orgSjablonen->count()} sjablonen)");
            $this->line(str_repeat('-', 60));
            
            foreach ($orgSjablonen as $sjabloon) {
                $actief = $sjabloon->is_actief ? '✅' : '❌';
                $this->line(sprintf("  %s ID:%d | %s | Cat: %s | Test: %s",
                    $actief,
                    $sjabloon->id,
                    $sjabloon->naam,
                    $sjabloon->categorie ?? 'N/A',
                    $sjabloon->testtype ?? 'N/A'
                ));
            }
            $this->newLine();
        }

        // 3. Analyseer categorieën en testtypes
        $this->info('📑 CATEGORIEËN OVERZICHT');
        $this->line(str_repeat('-', 60));
        $categories = DB::table('sjablonen')
            ->select('categorie', DB::raw('count(*) as aantal'))
            ->groupBy('categorie')
            ->orderBy('aantal', 'desc')
            ->get();

        foreach ($categories as $cat) {
            $catNaam = $cat->categorie ?? 'NULL';
            $this->line("  - {$catNaam}: {$cat->aantal} sjablonen");
        }

        $this->newLine();
        $this->info('🧪 TESTTYPES OVERZICHT');
        $this->line(str_repeat('-', 60));
        $testtypes = DB::table('sjablonen')
            ->select('testtype', DB::raw('count(*) as aantal'))
            ->groupBy('testtype')
            ->orderBy('aantal', 'desc')
            ->get();

        foreach ($testtypes as $type) {
            $typeName = $type->testtype ?? 'NULL';
            $this->line("  - {$typeName}: {$type->aantal} sjablonen");
        }

        // 4. Check organisatie 7 features
        $this->newLine();
        $this->info('🔑 ORGANISATIE 7 (LEVELUP) FEATURES');
        $this->line(str_repeat('-', 60));
        
        $org7 = DB::table('organisaties')->where('id', 7)->first();
        if ($org7) {
            $this->line("Organisatie: {$org7->naam}");
            $this->newLine();
            
            $org7Features = DB::table('organisatie_features')
                ->where('organisatie_id', 7)
                ->join('features', 'features.id', '=', 'organisatie_features.feature_id')
                ->select('features.key', 'features.naam', 'organisatie_features.is_actief')
                ->get();

            if ($org7Features->isEmpty()) {
                $this->warn('  ⚠️  GEEN FEATURES GEVONDEN!');
            } else {
                foreach ($org7Features as $feature) {
                    $status = $feature->is_actief ? '✅ ACTIEF' : '❌ INACTIEF';
                    $this->line("  - {$feature->key} ({$feature->naam}): {$status}");
                }
            }
        } else {
            $this->error('  ❌ Organisatie 7 niet gevonden!');
        }

        // 5. Suggesties voor mapping
        $this->newLine();
        $this->info('💡 MAPPING SUGGESTIES');
        $this->line(str_repeat('-', 60));
        $this->line('Op basis van categorie en testtype kunnen we sjablonen automatisch koppelen:');
        $this->newLine();

        $mappingVoorbeelden = [
            'Bikefit sjablonen' => ['categorie' => 'bikefit', 'required_feature' => null],
            'Inspanningstest sjablonen' => ['categorie' => 'inspanningstest', 'required_feature' => 'prestaties'],
            'Lactaat test sjablonen' => ['categorie' => 'inspanningstest', 'testtype' => 'lactaat', 'required_feature' => 'prestaties'],
            'VO2max sjablonen' => ['categorie' => 'inspanningstest', 'testtype' => 'vo2max', 'required_feature' => 'analytics'],
        ];

        foreach ($mappingVoorbeelden as $naam => $criteria) {
            $query = DB::table('sjablonen');
            if (isset($criteria['categorie'])) {
                $query->where('categorie', $criteria['categorie']);
            }
            if (isset($criteria['testtype'])) {
                $query->where('testtype', $criteria['testtype']);
            }
            $count = $query->count();
            
            $featureName = $criteria['required_feature'] ?? 'GEEN (altijd zichtbaar)';
            $this->line("  - {$naam} → Feature '{$featureName}' ({$count} gevonden)");
        }

        $this->newLine();
        $this->info('✅ ANALYSE COMPLEET');
        
        return Command::SUCCESS;
    }
}
