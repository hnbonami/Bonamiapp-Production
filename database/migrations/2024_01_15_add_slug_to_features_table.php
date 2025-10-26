<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Voeg slug kolom toe aan features tabel voor makkelijke referentie
     */
    public function up(): void
    {
        // Check of slug kolom al bestaat
        if (!Schema::hasColumn('features', 'slug')) {
            Schema::table('features', function (Blueprint $table) {
                $table->string('slug')->unique()->after('naam');
            });
        }
        
        // Genereer slugs voor bestaande features die nog geen slug hebben
        $features = DB::table('features')->whereNull('slug')->orWhere('slug', '')->get();
        foreach ($features as $feature) {
            DB::table('features')
                ->where('id', $feature->id)
                ->update([
                    'slug' => Str::slug($feature->naam)
                ]);
        }
    }

    /**
     * Reverse de migration
     */
    public function down(): void
    {
        Schema::table('features', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
