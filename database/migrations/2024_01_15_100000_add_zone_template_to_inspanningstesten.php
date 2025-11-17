<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inspanningstests', function (Blueprint $table) {
            // Voeg zone_template_id toe als foreign key naar trainings_zones_templates
            $table->foreignId('zone_template_id')->nullable()->after('trainingszones_data')->constrained('trainings_zones_templates')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('inspanningstests', function (Blueprint $table) {
            $table->dropForeign(['zone_template_id']);
            $table->dropColumn('zone_template_id');
        });
    }
};
