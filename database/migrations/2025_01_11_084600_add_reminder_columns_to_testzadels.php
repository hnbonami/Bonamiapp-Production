<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('testzadels', function (Blueprint $table) {
            if (!Schema::hasColumn('testzadels', 'herinnering_verstuurd')) {
                $table->boolean('herinnering_verstuurd')->default(false)->after('automatisch_mailtje');
            }
            if (!Schema::hasColumn('testzadels', 'herinnering_verstuurd_op')) {
                $table->timestamp('herinnering_verstuurd_op')->nullable()->after('herinnering_verstuurd');
            }
            if (!Schema::hasColumn('testzadels', 'laatste_herinnering')) {
                $table->timestamp('laatste_herinnering')->nullable()->after('herinnering_verstuurd_op');
            }
            if (!Schema::hasColumn('testzadels', 'werkelijke_retour_datum')) {
                $table->date('werkelijke_retour_datum')->nullable()->after('verwachte_retour_datum');
            }
        });
    }

    public function down()
    {
        Schema::table('testzadels', function (Blueprint $table) {
            $table->dropColumn([
                'herinnering_verstuurd', 
                'herinnering_verstuurd_op', 
                'laatste_herinnering',
                'werkelijke_retour_datum'
            ]);
        });
    }
};