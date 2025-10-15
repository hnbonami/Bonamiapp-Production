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
        Schema::table('inspanningstests', function (Blueprint $table) {
            // Check eerst of kolommen al bestaan voordat we ze toevoegen
            
            // User die test uitvoerde
            if (!Schema::hasColumn('inspanningstests', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('klant_id')->constrained()->onDelete('set null');
            }
            
            // Anaërobe drempel (aërobe bestaat al)
            if (!Schema::hasColumn('inspanningstests', 'anaerobe_drempel_vermogen')) {
                $table->decimal('anaerobe_drempel_vermogen', 8, 2)->nullable()->after('aerobe_drempel_hartslag');
            }
            
            if (!Schema::hasColumn('inspanningstests', 'anaerobe_drempel_hartslag')) {
                $table->decimal('anaerobe_drempel_hartslag', 8, 2)->nullable()->after('anaerobe_drempel_vermogen');
            }
            
            // Lichaamsmetingen (vetpercentage en buikomtrek bestaan al)
            if (!Schema::hasColumn('inspanningstests', 'lichaamsgewicht_kg')) {
                $table->decimal('lichaamsgewicht_kg', 5, 2)->nullable()->after('datum');
            }
            
            if (!Schema::hasColumn('inspanningstests', 'lichaamslengte_cm')) {
                $table->decimal('lichaamslengte_cm', 5, 2)->nullable()->after('lichaamsgewicht_kg');
            }
            
            if (!Schema::hasColumn('inspanningstests', 'bmi')) {
                $table->decimal('bmi', 4, 1)->nullable()->after('lichaamslengte_cm');
            }
            
            // Hartslaggegevens
            if (!Schema::hasColumn('inspanningstests', 'hartslag_rust_bpm')) {
                $table->integer('hartslag_rust_bpm')->nullable()->after('bmi');
            }
            
            if (!Schema::hasColumn('inspanningstests', 'maximale_hartslag_bpm')) {
                $table->integer('maximale_hartslag_bpm')->nullable()->after('hartslag_rust_bpm');
            }
            
            // Protocol configuratie velden
            if (!Schema::hasColumn('inspanningstests', 'testlocatie')) {
                $table->string('testlocatie')->nullable()->after('testtype');
            }
            
            if (!Schema::hasColumn('inspanningstests', 'protocol')) {
                $table->string('protocol')->nullable()->after('testlocatie');
            }
            
            if (!Schema::hasColumn('inspanningstests', 'startwattage')) {
                $table->integer('startwattage')->nullable()->after('protocol');
            }
            
            if (!Schema::hasColumn('inspanningstests', 'stappen_min')) {
                $table->integer('stappen_min')->nullable()->after('startwattage');
            }
            
            if (!Schema::hasColumn('inspanningstests', 'stappen_watt')) {
                $table->integer('stappen_watt')->nullable()->after('stappen_min');
            }
            
            // Extra velden die ontbreken
            if (!Schema::hasColumn('inspanningstests', 'weersomstandigheden')) {
                $table->string('weersomstandigheden')->nullable()->after('stappen_watt');
            }
            
            if (!Schema::hasColumn('inspanningstests', 'specifieke_doelstellingen')) {
                $table->text('specifieke_doelstellingen')->nullable()->after('weersomstandigheden');
            }
            
            if (!Schema::hasColumn('inspanningstests', 'analyse_methode')) {
                $table->string('analyse_methode')->nullable()->after('specifieke_doelstellingen');
            }
            
            if (!Schema::hasColumn('inspanningstests', 'zones_methode')) {
                $table->string('zones_methode')->nullable()->after('trainingszones_data');
            }
            
            if (!Schema::hasColumn('inspanningstests', 'zones_aantal')) {
                $table->integer('zones_aantal')->nullable()->after('zones_methode');
            }
            
            if (!Schema::hasColumn('inspanningstests', 'zones_eenheid')) {
                $table->string('zones_eenheid')->nullable()->after('zones_aantal');
            }
            
            // Hernoem data_punten naar testresultaten voor consistentie
            if (Schema::hasColumn('inspanningstests', 'data_punten') && !Schema::hasColumn('inspanningstests', 'testresultaten')) {
                $table->renameColumn('data_punten', 'testresultaten');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inspanningstests', function (Blueprint $table) {
            // Verwijder alleen kolommen die we hebben toegevoegd
            $kolommenOmTeVerwijderen = [
                'user_id',
                'anaerobe_drempel_vermogen',
                'anaerobe_drempel_hartslag',
                'lichaamsgewicht_kg',
                'lichaamslengte_cm',
                'bmi',
                'hartslag_rust_bpm',
                'maximale_hartslag_bpm',
                'testlocatie',
                'protocol',
                'startwattage',
                'stappen_min',
                'stappen_watt',
                'weersomstandigheden',
                'specifieke_doelstellingen',
                'analyse_methode',
                'zones_methode',
                'zones_aantal',
                'zones_eenheid',
            ];
            
            foreach ($kolommenOmTeVerwijderen as $kolom) {
                if (Schema::hasColumn('inspanningstests', $kolom)) {
                    if ($kolom === 'user_id') {
                        $table->dropForeign(['user_id']);
                    }
                    $table->dropColumn($kolom);
                }
            }
            
            // Hernoem testresultaten terug naar data_punten
            if (Schema::hasColumn('inspanningstests', 'testresultaten')) {
                $table->renameColumn('testresultaten', 'data_punten');
            }
        });
    }
};
