<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('staff_notes', function (Blueprint $table) {
            if (!Schema::hasColumn('staff_notes', 'sort_order')) {
                $table->integer('sort_order')->default(0)->after('id');
            }
            if (!Schema::hasColumn('staff_notes', 'image_path')) {
                $table->string('image_path')->nullable()->after('content');
            }
        });
    }

    public function down()
    {
        Schema::table('staff_notes', function (Blueprint $table) {
            $table->dropColumn(['sort_order', 'image_path']);
        });
    }
};