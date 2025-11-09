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
        Schema::table('email_templates', function (Blueprint $table) {
            // Check en voeg alleen kolommen toe die nog NIET bestaan
            
            // Voeg parent_template_id toe voor template overerving (kloon -> origineel link)
            if (!Schema::hasColumn('email_templates', 'parent_template_id')) {
                $table->unsignedBigInteger('parent_template_id')->nullable()->after('organisatie_id');
                \Log::info('✅ parent_template_id kolom toegevoegd');
            }
            
            // Voeg is_default toe om Performance Pulse standaard templates te markeren
            if (!Schema::hasColumn('email_templates', 'is_default')) {
                $table->boolean('is_default')->default(false)->after('is_active');
                \Log::info('✅ is_default kolom toegevoegd');
            }
        });
        
        // Voeg foreign key constraint toe (alleen als deze nog niet bestaat)
        try {
            if (Schema::hasColumn('email_templates', 'parent_template_id')) {
                // Check of de foreign key al bestaat door te proberen hem toe te voegen
                Schema::table('email_templates', function (Blueprint $table) {
                    // Probeer foreign key toe te voegen
                    $table->foreign('parent_template_id')
                          ->references('id')
                          ->on('email_templates')
                          ->onDelete('set null');
                });
                \Log::info('✅ Foreign key constraint toegevoegd voor parent_template_id');
            }
        } catch (\Exception $e) {
            // Foreign key bestaat waarschijnlijk al, geen probleem
            \Log::info('ℹ️  Foreign key constraint bestaat al of kon niet worden toegevoegd: ' . $e->getMessage());
        }
        
        // Update bestaande Performance Pulse templates (zonder organisatie_id) naar is_default = true
        if (Schema::hasColumn('email_templates', 'is_default')) {
            \DB::table('email_templates')
                ->whereNull('organisatie_id')
                ->update(['is_default' => true]);
            
            \Log::info('✅ Bestaande Performance Pulse templates gemarkeerd als is_default = true');
        }
        
        \Log::info('✅ Email templates migration voltooid');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('email_templates', function (Blueprint $table) {
            // Verwijder foreign key constraint eerst (alleen als deze bestaat)
            try {
                $table->dropForeign(['parent_template_id']);
            } catch (\Exception $e) {
                \Log::info('ℹ️  Foreign key bestaat niet, overslaan');
            }
            
            // Verwijder kolommen (alleen als ze bestaan)
            if (Schema::hasColumn('email_templates', 'parent_template_id')) {
                $table->dropColumn('parent_template_id');
            }
            
            if (Schema::hasColumn('email_templates', 'is_default')) {
                $table->dropColumn('is_default');
            }
        });
        
        \Log::info('✅ Email templates migration teruggedraaid');
    }
};
