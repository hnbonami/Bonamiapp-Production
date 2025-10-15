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
        // Create testzadel reminder template
        $templateContent = '
<h2>Beste {{klant_naam}},</h2>

<p>Dit is een vriendelijke herinnering dat je testzadel inmiddels ingeleverd had moeten worden.</p>

<p><strong>Details van je testzadel:</strong></p>
<ul>
    <li>Merk: {{testzadel_merk}}</li>
    <li>Model: {{testzadel_model}}</li>
    <li>Verwachte retour datum: {{retour_datum}}</li>
</ul>

<p>Graag zou je de testzadel zo snel mogelijk kunnen inleveren. Mocht je vragen hebben of hulp nodig hebben, neem dan gerust contact met ons op.</p>

<p>Bedankt voor je begrip!</p>

<p>Met vriendelijke groet,<br>
Het {{company_name}} team</p>
        ';

        // Try email_templates table first
        if (Schema::hasTable('email_templates')) {
            DB::table('email_templates')->insert([
                'name' => 'Testzadel Herinnering',
                'type' => 'testzadel_reminder',
                'subject' => 'Herinnering: Testzadel inleveren',
                'body_html' => $templateContent, // Verplicht veld
                'content' => $templateContent, // Voor backwards compatibility
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } 
        // Fallback to templates table
        elseif (Schema::hasTable('templates')) {
            DB::table('templates')->insert([
                'naam' => 'Testzadel Herinnering',
                'type' => 'testzadel_reminder',
                'inhoud' => $templateContent,
                'is_actief' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove testzadel reminder template
        if (Schema::hasTable('email_templates')) {
            DB::table('email_templates')->where('type', 'testzadel_reminder')->delete();
        }
        if (Schema::hasTable('templates')) {
            DB::table('templates')->where('type', 'testzadel_reminder')->delete();
        }
    }
};