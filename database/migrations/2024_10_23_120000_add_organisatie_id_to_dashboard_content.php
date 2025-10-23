<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check of kolom al bestaat
        if (!Schema::hasColumn('dashboard_content', 'organisatie_id')) {
            Schema::table('dashboard_content', function (Blueprint $table) {
                $table->unsignedBigInteger('organisatie_id')->nullable()->after('created_by');
                
                // Foreign key als organisaties tabel bestaat
                if (Schema::hasTable('organisaties')) {
                    $table->foreign('organisatie_id')
                          ->references('id')
                          ->on('organisaties')
                          ->onDelete('cascade');
                }
                
                $table->index('organisatie_id');
            });
            
            \Log::info('✅ Dashboard Content: organisatie_id kolom toegevoegd');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('dashboard_content', 'organisatie_id')) {
            Schema::table('dashboard_content', function (Blueprint $table) {
                $table->dropForeign(['organisatie_id']);
                $table->dropColumn('organisatie_id');
            });
        }
    }
};
