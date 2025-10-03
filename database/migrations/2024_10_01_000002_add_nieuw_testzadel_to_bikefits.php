<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('bikefits', function (Blueprint $table) {
            if (!Schema::hasColumn('bikefits', 'nieuw_testzadel')) {
                $table->string('nieuw_testzadel')->nullable()->after('zadelbreedte');
            }
        });
    }

    public function down()
    {
        Schema::table('bikefits', function (Blueprint $table) {
            if (Schema::hasColumn('bikefits', 'nieuw_testzadel')) {
                $table->dropColumn('nieuw_testzadel');
            }
        });
    }
};