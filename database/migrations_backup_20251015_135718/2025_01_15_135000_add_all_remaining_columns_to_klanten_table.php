<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('klanten', function (Blueprint $table) {
            // Controleer en voeg ALLE mogelijke ontbrekende kolommen toe
            
            // Basis contact info
            if (!Schema::hasColumn('klanten', 'telefoon')) {
                $table->string('telefoon')->nullable()->after('telefoonnummer');
            }
            if (!Schema::hasColumn('klanten', 'mobiel')) {
                $table->string('mobiel')->nullable()->after('telefoon');
            }
            if (!Schema::hasColumn('klanten', 'website')) {
                $table->string('website')->nullable()->after('email');
            }
            
            // Adres gegevens (extra velden)
            if (!Schema::hasColumn('klanten', 'adres')) {
                $table->string('adres')->nullable()->after('huisnummer');
            }
            if (!Schema::hasColumn('klanten', 'land')) {
                $table->string('land')->default('Nederland')->after('stad');
            }
            if (!Schema::hasColumn('klanten', 'provincie')) {
                $table->string('provincie')->nullable()->after('stad');
            }
            
            // Persoonlijke info
            if (!Schema::hasColumn('klanten', 'leeftijd')) {
                $table->integer('leeftijd')->nullable()->after('geboortedatum');
            }
            if (!Schema::hasColumn('klanten', 'lengte')) {
                $table->decimal('lengte', 5, 2)->nullable()->after('geslacht');
            }
            if (!Schema::hasColumn('klanten', 'gewicht')) {
                $table->decimal('gewicht', 5, 2)->nullable()->after('lengte');
            }
            if (!Schema::hasColumn('klanten', 'beroep')) {
                $table->string('beroep')->nullable()->after('gewicht');
            }
            
            // Sport gerelateerd
            if (!Schema::hasColumn('klanten', 'ervaring_jaren')) {
                $table->integer('ervaring_jaren')->nullable()->after('niveau');
            }
            if (!Schema::hasColumn('klanten', 'trainingsuren_per_week')) {
                $table->decimal('trainingsuren_per_week', 4, 1)->nullable()->after('ervaring_jaren');
            }
            if (!Schema::hasColumn('klanten', 'competitief')) {
                $table->boolean('competitief')->default(false)->after('trainingsuren_per_week');
            }
            if (!Schema::hasColumn('klanten', 'discipline')) {
                $table->string('discipline')->nullable()->after('sport');
            }
            
            // Gezondheid
            if (!Schema::hasColumn('klanten', 'allergieën')) {
                $table->text('allergieën')->nullable()->after('medische_geschiedenis');
            }
            if (!Schema::hasColumn('klanten', 'medicijnen')) {
                $table->text('medicijnen')->nullable()->after('allergieën');
            }
            if (!Schema::hasColumn('klanten', 'blessures')) {
                $table->text('blessures')->nullable()->after('medicijnen');
            }
            if (!Schema::hasColumn('klanten', 'huisarts')) {
                $table->string('huisarts')->nullable()->after('blessures');
            }
            if (!Schema::hasColumn('klanten', 'fysiotherapeut')) {
                $table->string('fysiotherapeut')->nullable()->after('huisarts');
            }
            
            // Administratief
            if (!Schema::hasColumn('klanten', 'klant_sinds')) {
                $table->date('klant_sinds')->nullable()->after('created_at');
            }
            if (!Schema::hasColumn('klanten', 'eerste_afspraak')) {
                $table->date('eerste_afspraak')->nullable()->after('klant_sinds');
            }
            if (!Schema::hasColumn('klanten', 'notities')) {
                $table->text('notities')->nullable()->after('doelen');
            }
            if (!Schema::hasColumn('klanten', 'referentie')) {
                $table->string('referentie')->nullable()->after('herkomst');
            }
            if (!Schema::hasColumn('klanten', 'actief')) {
                $table->boolean('actief')->default(true)->after('status');
            }
            
            // Contact voorkeuren
            if (!Schema::hasColumn('klanten', 'voorkeur_contact')) {
                $table->enum('voorkeur_contact', ['email', 'telefoon', 'sms', 'whatsapp'])->default('email')->after('telefoon');
            }
            if (!Schema::hasColumn('klanten', 'nieuwsbrief')) {
                $table->boolean('nieuwsbrief')->default(true)->after('voorkeur_contact');
            }
            if (!Schema::hasColumn('klanten', 'marketing_emails')) {
                $table->boolean('marketing_emails')->default(false)->after('nieuwsbrief');
            }
            
            // Financieel
            if (!Schema::hasColumn('klanten', 'btw_nummer')) {
                $table->string('btw_nummer')->nullable()->after('land');
            }
            if (!Schema::hasColumn('klanten', 'factuuradres_anders')) {
                $table->boolean('factuuradres_anders')->default(false)->after('btw_nummer');
            }
            if (!Schema::hasColumn('klanten', 'factuur_straat')) {
                $table->string('factuur_straat')->nullable()->after('factuuradres_anders');
            }
            if (!Schema::hasColumn('klanten', 'factuur_huisnummer')) {
                $table->string('factuur_huisnummer')->nullable()->after('factuur_straat');
            }
            if (!Schema::hasColumn('klanten', 'factuur_postcode')) {
                $table->string('factuur_postcode')->nullable()->after('factuur_huisnummer');
            }
            if (!Schema::hasColumn('klanten', 'factuur_stad')) {
                $table->string('factuur_stad')->nullable()->after('factuur_postcode');
            }
            
            // Emergency contact
            if (!Schema::hasColumn('klanten', 'noodcontact_naam')) {
                $table->string('noodcontact_naam')->nullable()->after('factuur_stad');
            }
            if (!Schema::hasColumn('klanten', 'noodcontact_telefoon')) {
                $table->string('noodcontact_telefoon')->nullable()->after('noodcontact_naam');
            }
            if (!Schema::hasColumn('klanten', 'noodcontact_relatie')) {
                $table->string('noodcontact_relatie')->nullable()->after('noodcontact_telefoon');
            }
            
            // Profiel
            if (!Schema::hasColumn('klanten', 'bio')) {
                $table->text('bio')->nullable()->after('notities');
            }
            if (!Schema::hasColumn('klanten', 'avatar_url')) {
                $table->string('avatar_url')->nullable()->after('avatar_path');
            }
            if (!Schema::hasColumn('klanten', 'social_media')) {
                $table->json('social_media')->nullable()->after('avatar_url');
            }
            
            // Timestamps extra
            if (!Schema::hasColumn('klanten', 'last_login')) {
                $table->timestamp('last_login')->nullable()->after('updated_at');
            }
            if (!Schema::hasColumn('klanten', 'deleted_at')) {
                $table->softDeletes()->after('last_login');
            }
        });
    }

    public function down()
    {
        Schema::table('klanten', function (Blueprint $table) {
            $columnsToRemove = [
                'telefoon', 'mobiel', 'website', 'adres', 'land', 'provincie',
                'leeftijd', 'lengte', 'gewicht', 'beroep', 'ervaring_jaren', 
                'trainingsuren_per_week', 'competitief', 'discipline',
                'allergieën', 'medicijnen', 'blessures', 'huisarts', 'fysiotherapeut',
                'klant_sinds', 'eerste_afspraak', 'notities', 'referentie', 'actief',
                'voorkeur_contact', 'nieuwsbrief', 'marketing_emails',
                'btw_nummer', 'factuuradres_anders', 'factuur_straat', 'factuur_huisnummer',
                'factuur_postcode', 'factuur_stad', 'noodcontact_naam', 'noodcontact_telefoon',
                'noodcontact_relatie', 'bio', 'avatar_url', 'social_media', 'last_login', 'deleted_at'
            ];
            
            foreach ($columnsToRemove as $column) {
                if (Schema::hasColumn('klanten', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};