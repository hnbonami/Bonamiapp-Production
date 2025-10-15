<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('klanten', function (Blueprint $table) {
            // Check en voeg alle ontbrekende kolommen toe
            if (!Schema::hasColumn('klanten', 'telefoonnummer')) {
                $table->string('telefoonnummer')->nullable()->after('email');
            }
            if (!Schema::hasColumn('klanten', 'straatnaam')) {
                $table->string('straatnaam')->nullable()->after('telefoonnummer');
            }
            if (!Schema::hasColumn('klanten', 'huisnummer')) {
                $table->string('huisnummer')->nullable()->after('straatnaam');
            }
            if (!Schema::hasColumn('klanten', 'stad')) {
                $table->string('stad')->nullable()->after('postcode');
            }
            if (!Schema::hasColumn('klanten', 'club')) {
                $table->string('club')->nullable()->after('niveau');
            }
            if (!Schema::hasColumn('klanten', 'herkomst')) {
                $table->string('herkomst')->nullable()->after('club');
            }
            if (!Schema::hasColumn('klanten', 'status')) {
                $table->enum('status', ['Actief', 'Inactief', 'Prospect'])->default('Actief')->after('herkomst');
            }
            if (!Schema::hasColumn('klanten', 'laatste_afspraak')) {
                $table->date('laatste_afspraak')->nullable()->after('status');
            }
            if (!Schema::hasColumn('klanten', 'avatar_path')) {
                $table->string('avatar_path')->nullable()->after('laatste_afspraak');
            }
            
            // Extra kolommen die mogelijk nodig zijn
            if (!Schema::hasColumn('klanten', 'medische_geschiedenis')) {
                $table->text('medische_geschiedenis')->nullable()->after('niveau');
            }
            if (!Schema::hasColumn('klanten', 'doelen')) {
                $table->text('doelen')->nullable()->after('medische_geschiedenis');
            }
            if (!Schema::hasColumn('klanten', 'telefoon')) {
                $table->string('telefoon')->nullable()->after('telefoonnummer');
            }
            if (!Schema::hasColumn('klanten', 'avatar')) {
                $table->string('avatar')->nullable()->after('avatar_path');
            }
        });
    }

    public function down()
    {
        Schema::table('klanten', function (Blueprint $table) {
            $columns = [
                'telefoonnummer', 'straatnaam', 'huisnummer', 'stad', 'club', 
                'herkomst', 'status', 'laatste_afspraak', 'avatar_path',
                'medische_geschiedenis', 'doelen', 'telefoon', 'avatar'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('klanten', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};