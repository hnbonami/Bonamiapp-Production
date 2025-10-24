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
        Schema::table('email_triggers', function (Blueprint $table) {
            // Voeg organisatie_id toe als foreign key
            $table->unsignedBigInteger('organisatie_id')->nullable()->after('id');
            $table->foreign('organisatie_id')->references('id')->on('organisaties')->onDelete('cascade');
            
            // Index voor betere performance
            $table->index(['organisatie_id', 'trigger_type', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('email_triggers', function (Blueprint $table) {
            $table->dropForeign(['organisatie_id']);
            $table->dropIndex(['organisatie_id', 'trigger_type', 'is_active']);
            $table->dropColumn('organisatie_id');
        });
    }
};
