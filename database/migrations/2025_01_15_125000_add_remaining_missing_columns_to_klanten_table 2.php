<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('klanten', function (Blueprint $table) {
            // Voeg alleen kolommen toe die ECHT ontbreken
            if (!Schema::hasColumn('klanten', 'medische_geschiedenis')) {
                $table->text('medische_geschiedenis')->nullable()->after('niveau');
            }
            if (!Schema::hasColumn('klanten', 'doelen')) {
                $table->text('doelen')->nullable()->after('medische_geschiedenis');
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
            if (!Schema::hasColumn('klanten', 'avatar')) {
                $table->string('avatar')->nullable()->after('avatar_path');
            }
        });
    }

    public function down()
    {
        Schema::table('klanten', function (Blueprint $table) {
            $columns = [
                'medische_geschiedenis', 'doelen', 'status', 
                'laatste_afspraak', 'avatar_path', 'avatar'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('klanten', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};