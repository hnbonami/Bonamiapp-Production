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
        Schema::table('klanten', function (Blueprint $table) {
            // Voeg doorverwijskolommen toe als ze nog niet bestaan
            if (!Schema::hasColumn('klanten', 'doorverwijzing_type')) {
                $table->string('doorverwijzing_type', 50)->nullable()->after('opmerkingen');
            }
            if (!Schema::hasColumn('klanten', 'doorverwijzing_klant_id')) {
                $table->unsignedBigInteger('doorverwijzing_klant_id')->nullable()->after('doorverwijzing_type');
                $table->foreign('doorverwijzing_klant_id')->references('id')->on('klanten')->onDelete('set null');
            }
            if (!Schema::hasColumn('klanten', 'doorverwijzing_processed')) {
                $table->boolean('doorverwijzing_processed')->default(false)->after('doorverwijzing_klant_id');
            }
            if (!Schema::hasColumn('klanten', 'doorverwijzing_datum')) {
                $table->timestamp('doorverwijzing_datum')->nullable()->after('doorverwijzing_processed');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('klanten', function (Blueprint $table) {
            // Verwijder foreign key eerst
            if (Schema::hasColumn('klanten', 'doorverwijzing_klant_id')) {
                $table->dropForeign(['doorverwijzing_klant_id']);
            }
            
            // Verwijder kolommen
            $table->dropColumn([
                'doorverwijzing_type',
                'doorverwijzing_klant_id', 
                'doorverwijzing_processed',
                'doorverwijzing_datum'
            ]);
        });
    }
};
