<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Check if columns exist before adding them
        if (!Schema::hasColumn('klanten', 'user_id')) {
            Schema::table('klanten', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            });
        }

        if (!Schema::hasColumn('medewerkers', 'user_id')) {
            Schema::table('medewerkers', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            });
        }
    }

    public function down()
    {
        Schema::table('klanten', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });

        Schema::table('medewerkers', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};