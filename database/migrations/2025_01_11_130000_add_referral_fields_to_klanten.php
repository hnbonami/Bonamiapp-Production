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
        if (!Schema::hasColumn('klanten', 'doorverwijzing_type')) {
            Schema::table('klanten', function (Blueprint $table) {
                $table->string('doorverwijzing_type')->nullable();
                $table->unsignedBigInteger('doorverwijzing_klant_id')->nullable();
                $table->boolean('doorverwijzing_processed')->default(false);
            });
        }
        
        // Add foreign key if it doesn't exist
        if (Schema::hasColumn('klanten', 'doorverwijzing_klant_id')) {
            try {
                Schema::table('klanten', function (Blueprint $table) {
                    $table->foreign('doorverwijzing_klant_id')->references('id')->on('klanten')->onDelete('set null');
                });
            } catch (\Exception $e) {
                // Foreign key might already exist - continue
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('klanten', function (Blueprint $table) {
            if (Schema::hasColumn('klanten', 'doorverwijzing_klant_id')) {
                try {
                    $table->dropForeign(['doorverwijzing_klant_id']);
                } catch (\Exception $e) {
                    // Ignore if foreign key doesn't exist
                }
            }
            
            $columns = ['doorverwijzing_type', 'doorverwijzing_klant_id', 'doorverwijzing_processed'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('klanten', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};