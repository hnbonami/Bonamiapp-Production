<?php

/**
 * Sjablonen Database Analyse Script
 * 
 * Run met: php artisan tinker < database/analyze_sjablonen.php
 */

echo "=== 🔍 SJABLONEN DATABASE ANALYSE ===\n\n";

// 1. Check database structuur
echo "📊 DATABASE STRUCTUUR\n";
echo "--------------------\n";
$columns = Schema::getColumnListing('sjablonen');
echo "Kolommen in sjablonen tabel:\n";
foreach ($columns as $column) {
    $type = DB::select("SHOW COLUMNS FROM sjablonen WHERE Field = ?", [$column])[0]->Type;
    echo sprintf("  - %s (%s)\n", $column, $type);
}

// 2. Alle sjablonen ophalen
echo "\n📋 ALLE SJABLONEN IN DATABASE\n";
echo "-----------------------------\n";
$sjablonen = DB::table('sjablonen')
    ->select('id', 'naam', 'categorie', 'testtype', 'organisatie_id', 'is_actief')
    ->orderBy('organisatie_id')
    ->orderBy('naam')
    ->get();

echo sprintf("Totaal aantal sjablonen: %d\n\n", $sjablonen->count());

$groepeerd = $sjablonen->groupBy('organisatie_id');
foreach ($groepeerd as $orgId => $orgSjablonen) {
    $org = DB::table('organisaties')->where('id', $orgId)->first();
    $orgNaam = $org ? $org->naam : 'Onbekend';
    
    echo sprintf("🏢 ORGANISATIE %d - %s (%d sjablonen)\n", $orgId, $orgNaam, $orgSjablonen->count());
    echo str_repeat("-", 60) . "\n";
    
    foreach ($orgSjablonen as $sjabloon) {
        $actief = $sjabloon->is_actief ? '✅' : '❌';
        echo sprintf("  %s ID:%d | %s | Cat: %s | Test: %s\n",
            $actief,
            $sjabloon->id,
            $sjabloon->naam,
            $sjabloon->categorie ?? 'N/A',
            $sjabloon->testtype ?? 'N/A'
        );
    }
    echo "\n";
}

// 3. Analyseer categorieën en testtypes
echo "📑 CATEGORIEËN OVERZICHT\n";
echo "------------------------\n";
$categories = DB::table('sjablonen')
    ->select('categorie', DB::raw('count(*) as aantal'))
    ->groupBy('categorie')
    ->orderBy('aantal', 'desc')
    ->get();

foreach ($categories as $cat) {
    echo sprintf("  - %s: %d sjablonen\n", $cat->categorie ?? 'NULL', $cat->aantal);
}

echo "\n🧪 TESTTYPES OVERZICHT\n";
echo "----------------------\n";
$testtypes = DB::table('sjablonen')
    ->select('testtype', DB::raw('count(*) as aantal'))
    ->groupBy('testtype')
    ->orderBy('aantal', 'desc')
    ->get();

foreach ($testtypes as $type) {
    echo sprintf("  - %s: %d sjablonen\n", $type->testtype ?? 'NULL', $type->aantal);
}

// 4. Check organisatie 7 features
echo "\n🔑 ORGANISATIE 7 (LEVELUP) FEATURES\n";
echo "-----------------------------------\n";
$org7Features = DB::table('organisatie_features')
    ->where('organisatie_id', 7)
    ->join('features', 'features.id', '=', 'organisatie_features.feature_id')
    ->select('features.key', 'features.naam', 'organisatie_features.is_actief')
    ->get();

foreach ($org7Features as $feature) {
    $status = $feature->is_actief ? '✅ ACTIEF' : '❌ INACTIEF';
    echo sprintf("  - %s (%s): %s\n", $feature->key, $feature->naam, $status);
}

// 5. Suggesties voor mapping
echo "\n💡 MAPPING SUGGESTIES\n";
echo "--------------------\n";
echo "Op basis van categorie en testtype kunnen we sjablonen automatisch koppelen:\n\n";

$mappingVoorbeelden = [
    'bikefit' => ['categorie' => 'bikefit', 'required_feature' => null],
    'inspanningstest' => ['categorie' => 'inspanningstest', 'required_feature' => 'prestaties'],
    'lactaat' => ['categorie' => 'inspanningstest', 'testtype' => 'lactaat', 'required_feature' => 'prestaties'],
    'vo2max' => ['categorie' => 'inspanningstest', 'testtype' => 'vo2max', 'required_feature' => 'analytics'],
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
    
    echo sprintf("  - %s sjablonen → Feature '%s' (%d gevonden)\n",
        ucfirst($naam),
        $criteria['required_feature'] ?? 'GEEN (altijd zichtbaar)',
        $count
    );
}

echo "\n✅ ANALYSE COMPLEET\n";
