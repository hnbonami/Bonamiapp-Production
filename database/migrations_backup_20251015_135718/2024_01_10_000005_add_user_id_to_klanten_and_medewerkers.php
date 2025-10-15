<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Add user_id to klanten table
        if (!Schema::hasColumn('klanten', 'user_id')) {
            Schema::table('klanten', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null')->after('id');
                $table->index('user_id');
            });
        }

        // Add user_id to medewerkers table  
        if (!Schema::hasColumn('medewerkers', 'user_id')) {
            Schema::table('medewerkers', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null')->after('id');
                $table->index('user_id');
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