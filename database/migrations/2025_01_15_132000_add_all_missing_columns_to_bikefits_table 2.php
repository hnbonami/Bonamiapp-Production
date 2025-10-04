<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('bikefits', function (Blueprint $table) {
            // Check en voeg alle mogelijke ontbrekende kolommen toe
            if (!Schema::hasColumn('bikefits', 'klant_id')) {
                $table->unsignedBigInteger('klant_id')->after('id');
            }
            if (!Schema::hasColumn('bikefits', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('klant_id');
            }
            if (!Schema::hasColumn('bikefits', 'datum')) {
                $table->timestamp('datum')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('bikefits', 'testtype')) {
                $table->string('testtype')->nullable()->after('datum');
            }
            if (!Schema::hasColumn('bikefits', 'type_fitting')) {
                $table->string('type_fitting')->nullable()->after('testtype');
            }
            
            // Fiets info
            if (!Schema::hasColumn('bikefits', 'fietsmerk')) {
                $table->string('fietsmerk')->nullable()->after('type_fitting');
            }
            if (!Schema::hasColumn('bikefits', 'kadermaat')) {
                $table->string('kadermaat')->nullable()->after('fietsmerk');
            }
            if (!Schema::hasColumn('bikefits', 'bouwjaar')) {
                $table->integer('bouwjaar')->nullable()->after('kadermaat');
            }
            if (!Schema::hasColumn('bikefits', 'type_fiets')) {
                $table->string('type_fiets')->nullable()->after('bouwjaar');
            }
            if (!Schema::hasColumn('bikefits', 'frametype')) {
                $table->string('frametype')->nullable()->after('type_fiets');
            }
            
            // Lichaamsmaten
            if (!Schema::hasColumn('bikefits', 'lengte_cm')) {
                $table->decimal('lengte_cm', 5, 2)->nullable()->after('frametype');
            }
            if (!Schema::hasColumn('bikefits', 'binnenbeenlengte_cm')) {
                $table->decimal('binnenbeenlengte_cm', 5, 2)->nullable()->after('lengte_cm');
            }
            if (!Schema::hasColumn('bikefits', 'armlengte_cm')) {
                $table->decimal('armlengte_cm', 5, 2)->nullable()->after('binnenbeenlengte_cm');
            }
            if (!Schema::hasColumn('bikefits', 'romplengte_cm')) {
                $table->decimal('romplengte_cm', 5, 2)->nullable()->after('armlengte_cm');
            }
            if (!Schema::hasColumn('bikefits', 'schouderbreedte_cm')) {
                $table->decimal('schouderbreedte_cm', 5, 2)->nullable()->after('romplengte_cm');
            }
            
            // Zitpositie
            if (!Schema::hasColumn('bikefits', 'zadel_trapas_hoek')) {
                $table->decimal('zadel_trapas_hoek', 5, 2)->nullable()->after('schouderbreedte_cm');
            }
            if (!Schema::hasColumn('bikefits', 'zadel_trapas_afstand')) {
                $table->decimal('zadel_trapas_afstand', 5, 2)->nullable()->after('zadel_trapas_hoek');
            }
            if (!Schema::hasColumn('bikefits', 'stuur_trapas_hoek')) {
                $table->decimal('stuur_trapas_hoek', 5, 2)->nullable()->after('zadel_trapas_afstand');
            }
            if (!Schema::hasColumn('bikefits', 'stuur_trapas_afstand')) {
                $table->decimal('stuur_trapas_afstand', 5, 2)->nullable()->after('stuur_trapas_hoek');
            }
            if (!Schema::hasColumn('bikefits', 'zadel_lengte_center_top')) {
                $table->decimal('zadel_lengte_center_top', 5, 2)->nullable()->after('stuur_trapas_afstand');
            }
            
            // Aanpassingen
            if (!Schema::hasColumn('bikefits', 'aanpassingen_zadel')) {
                $table->decimal('aanpassingen_zadel', 5, 2)->nullable()->after('zadel_lengte_center_top');
            }
            if (!Schema::hasColumn('bikefits', 'aanpassingen_setback')) {
                $table->decimal('aanpassingen_setback', 5, 2)->nullable()->after('aanpassingen_zadel');
            }
            if (!Schema::hasColumn('bikefits', 'aanpassingen_reach')) {
                $table->decimal('aanpassingen_reach', 5, 2)->nullable()->after('aanpassingen_setback');
            }
            if (!Schema::hasColumn('bikefits', 'aanpassingen_drop')) {
                $table->decimal('aanpassingen_drop', 5, 2)->nullable()->after('aanpassingen_reach');
            }
            
            // Stuurpen
            if (!Schema::hasColumn('bikefits', 'aanpassingen_stuurpen_aan')) {
                $table->boolean('aanpassingen_stuurpen_aan')->default(false)->after('aanpassingen_drop');
            }
            if (!Schema::hasColumn('bikefits', 'aanpassingen_stuurpen_pre')) {
                $table->decimal('aanpassingen_stuurpen_pre', 5, 2)->nullable()->after('aanpassingen_stuurpen_aan');
            }
            if (!Schema::hasColumn('bikefits', 'aanpassingen_stuurpen_post')) {
                $table->decimal('aanpassingen_stuurpen_post', 5, 2)->nullable()->after('aanpassingen_stuurpen_pre');
            }
            
            // Zadel
            if (!Schema::hasColumn('bikefits', 'type_zadel')) {
                $table->string('type_zadel')->nullable()->after('aanpassingen_stuurpen_post');
            }
            if (!Schema::hasColumn('bikefits', 'zadeltil')) {
                $table->decimal('zadeltil', 5, 2)->nullable()->after('type_zadel');
            }
            if (!Schema::hasColumn('bikefits', 'zadelbreedte')) {
                $table->decimal('zadelbreedte', 5, 2)->nullable()->after('zadeltil');
            }
            if (!Schema::hasColumn('bikefits', 'nieuw_testzadel')) {
                $table->string('nieuw_testzadel')->nullable()->after('zadelbreedte');
            }
            
            // Schoenplaatjes
            if (!Schema::hasColumn('bikefits', 'rotatie_aanpassingen')) {
                $table->string('rotatie_aanpassingen')->nullable()->after('nieuw_testzadel');
            }
            if (!Schema::hasColumn('bikefits', 'inclinatie_aanpassingen')) {
                $table->string('inclinatie_aanpassingen')->nullable()->after('rotatie_aanpassingen');
            }
            if (!Schema::hasColumn('bikefits', 'ophoging_li')) {
                $table->decimal('ophoging_li', 5, 2)->nullable()->after('inclinatie_aanpassingen');
            }
            if (!Schema::hasColumn('bikefits', 'ophoging_re')) {
                $table->decimal('ophoging_re', 5, 2)->nullable()->after('ophoging_li');
            }
            
            // Anamnese
            if (!Schema::hasColumn('bikefits', 'algemene_klachten')) {
                $table->text('algemene_klachten')->nullable()->after('ophoging_re');
            }
            if (!Schema::hasColumn('bikefits', 'beenlengteverschil')) {
                $table->boolean('beenlengteverschil')->default(false)->after('algemene_klachten');
            }
            if (!Schema::hasColumn('bikefits', 'beenlengteverschil_cm')) {
                $table->string('beenlengteverschil_cm')->nullable()->after('beenlengteverschil');
            }
            if (!Schema::hasColumn('bikefits', 'lenigheid_hamstrings')) {
                $table->string('lenigheid_hamstrings')->nullable()->after('beenlengteverschil_cm');
            }
            if (!Schema::hasColumn('bikefits', 'steunzolen')) {
                $table->boolean('steunzolen')->default(false)->after('lenigheid_hamstrings');
            }
            if (!Schema::hasColumn('bikefits', 'steunzolen_reden')) {
                $table->string('steunzolen_reden')->nullable()->after('steunzolen');
            }
            
            // Voetmeting
            if (!Schema::hasColumn('bikefits', 'schoenmaat')) {
                $table->integer('schoenmaat')->nullable()->after('steunzolen_reden');
            }
            if (!Schema::hasColumn('bikefits', 'voetbreedte')) {
                $table->decimal('voetbreedte', 4, 2)->nullable()->after('schoenmaat');
            }
            if (!Schema::hasColumn('bikefits', 'voetpositie')) {
                $table->enum('voetpositie', ['neutraal', 'pronatie', 'supinatie'])->nullable()->after('voetbreedte');
            }
            
            // Template
            if (!Schema::hasColumn('bikefits', 'template_kind')) {
                $table->string('template_kind')->nullable()->after('voetpositie');
            }
            
            // Mobiliteit testen
            $mobilityTests = [
                'straight_leg_raise_links', 'straight_leg_raise_rechts',
                'knieflexie_links', 'knieflexie_rechts',
                'heup_endorotatie_links', 'heup_endorotatie_rechts',
                'heup_exorotatie_links', 'heup_exorotatie_rechts',
                'enkeldorsiflexie_links', 'enkeldorsiflexie_rechts',
                'one_leg_squat_links', 'one_leg_squat_rechts'
            ];
            
            foreach ($mobilityTests as $test) {
                if (!Schema::hasColumn('bikefits', $test)) {
                    $table->string($test)->nullable()->after('template_kind');
                }
            }
            
            // Opmerkingen
            if (!Schema::hasColumn('bikefits', 'opmerkingen')) {
                $table->text('opmerkingen')->nullable();
            }
            if (!Schema::hasColumn('bikefits', 'interne_opmerkingen')) {
                $table->text('interne_opmerkingen')->nullable();
            }
        });
        
        // Foreign keys toevoegen als ze nog niet bestaan
        if (!Schema::hasColumn('bikefits', 'klant_id') || !DB::select("SHOW CREATE TABLE bikefits")[0]->{'Create Table'}) {
            Schema::table('bikefits', function (Blueprint $table) {
                $table->foreign('klant_id')->references('id')->on('klanten')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            });
        }
    }

    public function down()
    {
        // Drop alle toegevoegde kolommen
        $columnsToRemove = [
            'klant_id', 'user_id', 'datum', 'testtype', 'type_fitting',
            'fietsmerk', 'kadermaat', 'bouwjaar', 'type_fiets', 'frametype',
            'lengte_cm', 'binnenbeenlengte_cm', 'armlengte_cm', 'romplengte_cm', 'schouderbreedte_cm',
            'zadel_trapas_hoek', 'zadel_trapas_afstand', 'stuur_trapas_hoek', 'stuur_trapas_afstand', 'zadel_lengte_center_top',
            'aanpassingen_zadel', 'aanpassingen_setback', 'aanpassingen_reach', 'aanpassingen_drop',
            'aanpassingen_stuurpen_aan', 'aanpassingen_stuurpen_pre', 'aanpassingen_stuurpen_post',
            'type_zadel', 'zadeltil', 'zadelbreedte', 'nieuw_testzadel',
            'rotatie_aanpassingen', 'inclinatie_aanpassingen', 'ophoging_li', 'ophoging_re',
            'algemene_klachten', 'beenlengteverschil', 'beenlengteverschil_cm', 'lenigheid_hamstrings', 'steunzolen', 'steunzolen_reden',
            'schoenmaat', 'voetbreedte', 'voetpositie', 'template_kind',
            'straight_leg_raise_links', 'straight_leg_raise_rechts', 'knieflexie_links', 'knieflexie_rechts',
            'heup_endorotatie_links', 'heup_endorotatie_rechts', 'heup_exorotatie_links', 'heup_exorotatie_rechts',
            'enkeldorsiflexie_links', 'enkeldorsiflexie_rechts', 'one_leg_squat_links', 'one_leg_squat_rechts',
            'opmerkingen', 'interne_opmerkingen'
        ];
        
        Schema::table('bikefits', function (Blueprint $table) use ($columnsToRemove) {
            foreach ($columnsToRemove as $column) {
                if (Schema::hasColumn('bikefits', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};