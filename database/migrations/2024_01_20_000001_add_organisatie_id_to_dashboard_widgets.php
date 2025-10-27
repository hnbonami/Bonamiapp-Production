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
        // Voeg organisatie_id toe aan dashboard_widgets als deze nog niet bestaat
        if (!Schema::hasColumn('dashboard_widgets', 'organisatie_id')) {
            Schema::table('dashboard_widgets', function (Blueprint $table) {
                $table->unsignedBigInteger('organisatie_id')->nullable()->after('created_by');
                $table->foreign('organisatie_id')->references('id')->on('organisaties')->onDelete('cascade');
                $table->index('organisatie_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dashboard_widgets', function (Blueprint $table) {
            $table->dropForeign(['organisatie_id']);
            $table->dropColumn('organisatie_id');
        });
    }
};
