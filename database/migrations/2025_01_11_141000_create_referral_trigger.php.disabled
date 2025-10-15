<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Insert referral trigger with correct column names
        DB::table('email_triggers')->insert([
            'name' => 'Doorverwijzing Bedanking',
            'type' => 'customer_referral',
            'email_template_id' => DB::table('templates')->where('type', 'referral_thank_you')->value('id'),
            'is_active' => true,
            'conditions' => json_encode([
                'doorverwijzing_type' => 'mond_aan_mond',
                'doorverwijzing_klant_id' => 'not_null'
            ]),
            'settings' => json_encode([
                'delay_minutes' => 0
            ]),
            'emails_sent' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('email_triggers')->where('trigger_type', 'customer_referral')->delete();
    }
};