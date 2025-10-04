<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('medewerkers', function (Blueprint $table) {
            // Voeg deleted_at kolom toe voor SoftDeletes
            if (!Schema::hasColumn('medewerkers', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }
        });
    }

    public function down()
    {
        Schema::table('medewerkers', function (Blueprint $table) {
            if (Schema::hasColumn('medewerkers', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};