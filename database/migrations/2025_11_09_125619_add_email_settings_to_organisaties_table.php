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
        Schema::table('organisaties', function (Blueprint $table) {
            // Email settings voor organisatie - voeg alleen toe als ze nog niet bestaan
            if (!Schema::hasColumn('organisaties', 'bedrijf_naam')) {
                $table->string('bedrijf_naam')->nullable()->after('naam');
            }
            if (!Schema::hasColumn('organisaties', 'email_from_name')) {
                $table->string('email_from_name')->nullable();
            }
            if (!Schema::hasColumn('organisaties', 'email_from_address')) {
                $table->string('email_from_address')->nullable();
            }
            if (!Schema::hasColumn('organisaties', 'website_url')) {
                $table->string('website_url')->nullable();
            }
            if (!Schema::hasColumn('organisaties', 'email_signature')) {
                $table->text('email_signature')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organisaties', function (Blueprint $table) {
            $table->dropColumn([
                'bedrijf_naam',
                'email_from_name',
                'email_from_address',
                'email_signature'
            ]);
            
            // Only drop website_url if we added it
            if (Schema::hasColumn('organisaties', 'website_url')) {
                // Check if it was added by this migration
                // For safety, we'll keep it in rollback
            }
        });
    }
};
