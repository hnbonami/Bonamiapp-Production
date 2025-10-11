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
        // VEILIG: Check if referral_thank_you template already exists
        $existingTemplate = DB::table('email_templates')
            ->where('type', 'referral_thank_you')
            ->first();

        if ($existingTemplate) {
            \Log::info('✅ Referral thank you template already exists - skipping');
            return;
        }

        \Log::info('🔨 Creating referral thank you email template...');

        // Get the template content
        $templatePath = resource_path('views/emails/templates/referral-thank-you.blade.php');
        $templateContent = file_exists($templatePath) ? file_get_contents($templatePath) : '';

        if (empty($templateContent)) {
            \Log::warning('⚠️ Template file not found, using fallback content');
            $templateContent = '<h1>Bedankt voor uw doorverwijzing!</h1><p>Beste @{{voornaam}} @{{naam}},</p><p>@{{referred_customer_name}} heeft u genoemd als doorverwijzer. Hartelijk dank!</p>';
        }

        // Create referral thank you template
        DB::table('email_templates')->insert([
            'name' => 'Bedankmail Doorverwijzing',
            'type' => 'referral_thank_you',
            'subject' => '🤝 Bedankt voor uw doorverwijzing naar Bonami Sportcoaching!',
            'body_html' => $templateContent,
            'body_text' => 'Beste @{{voornaam}} @{{naam}}, @{{referred_customer_name}} heeft u genoemd als doorverwijzer naar Bonami Sportcoaching. Hartelijk dank voor uw vertrouwen!',
            'description' => 'Automatische bedankmail voor klanten die iemand hebben doorverwezen',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        \Log::info('✅ Referral thank you template created successfully');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('email_templates')
            ->where('type', 'referral_thank_you')
            ->delete();
    }
};