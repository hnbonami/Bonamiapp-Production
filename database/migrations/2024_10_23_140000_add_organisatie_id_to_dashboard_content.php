<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Deze migratie is niet meer nodig - organisatie_id zit al in create_dashboard_content_table
        // Check eerst of tabel bestaat
        if (!Schema::hasTable('dashboard_content')) {
            \Log::info('⚠️  Tabel dashboard_content bestaat niet - skip deze migratie');
            return;
        }
        
        // Check of kolom al bestaat
        if (!Schema::hasColumn('dashboard_content', 'organisatie_id')) {
            Schema::table('dashboard_content', function (Blueprint $table) {
                $table->unsignedBigInteger('organisatie_id')->nullable()->after('created_by');
                $table->index('organisatie_id');
                
                if (Schema::hasTable('organisaties')) {
                    $table->foreign('organisatie_id')
                          ->references('id')
                          ->on('organisaties')
                          ->onDelete('cascade');
                }
            });
            
            \Log::info('✅ Migratie: organisatie_id kolom toegevoegd aan dashboard_content');
        } else {
            \Log::info('ℹ️  Migratie: organisatie_id kolom bestaat al');
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('dashboard_content', 'organisatie_id')) {
            Schema::table('dashboard_content', function (Blueprint $table) {
                // Verwijder foreign key eerst (als die bestaat)
                if (Schema::hasTable('organisaties')) {
                    $table->dropForeign(['organisatie_id']);
                }
                $table->dropColumn('organisatie_id');
            });
        }
    }
};
