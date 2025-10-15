<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('staff_notes', function (Blueprint $table) {
            if (!Schema::hasColumn('staff_notes', 'visibility')) {
                $table->enum('visibility', ['all', 'staff_only'])->default('all')->after('content');
            }
        });
    }

    public function down()
    {
        Schema::table('staff_notes', function (Blueprint $table) {
            $table->dropColumn('visibility');
        });
    }
};