<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // VEILIG: Check if referral_thank_you trigger already exists
        $existingTrigger = DB::table('email_triggers')
            ->where('type', 'referral_thank_you')
            ->first();

        if ($existingTrigger) {
            \Log::info('✅ Referral thank you trigger already exists - skipping');
            return;
        }

        \Log::info('🔨 Creating referral thank you email trigger...');

        // First get the template ID
        $templateId = DB::table('email_templates')
            ->where('type', 'referral_thank_you')
            ->value('id');

        if (!$templateId) {
            \Log::error('❌ Referral template not found, cannot create trigger');
            throw new \Exception('Referral thank you template must be created first');
        }

        // Create referral thank you trigger
        DB::table('email_triggers')->insert([
            'type' => 'referral_thank_you',
            'name' => 'Doorverwijzing Bedankmail',
            'email_template_id' => $templateId,
            'conditions' => json_encode(['trigger_on' => 'referral_created', 'referral_type' => 'mond_aan_mond']),
            'is_active' => true,
            'emails_sent' => 0,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        \Log::info('✅ Referral thank you trigger created successfully');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('email_triggers')
            ->where('type', 'referral_thank_you')
            ->delete();
    }
};