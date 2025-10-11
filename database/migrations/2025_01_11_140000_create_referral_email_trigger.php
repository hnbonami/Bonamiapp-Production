<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Insert email trigger voor doorverwijzing bedanking
        DB::table('email_triggers')->insert([
            [
                'name' => 'Doorverwijzing Bedanking',
                'event_type' => 'customer_referral',
                'description' => 'Bedankingsmail naar klant die een nieuwe klant heeft doorverwezen',
                'is_active' => true,
                'conditions' => json_encode([
                    'doorverwijzing_type' => 'mond_aan_mond',
                    'has_referring_customer' => true
                ]),
                'delay_minutes' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }

    public function down(): void
    {
        DB::table('email_triggers')
            ->where('event_type', 'customer_referral')
            ->delete();
    }
};