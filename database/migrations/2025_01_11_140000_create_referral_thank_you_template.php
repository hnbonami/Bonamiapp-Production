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
        // Insert referral thank you template with correct column names
        DB::table('templates')->insert([
            'naam' => 'Bedanking Doorverwijzing',
            'type' => 'referral_thank_you',
            'inhoud' => '
<h2>Beste {{referring_customer_name}},</h2>

<p>Hartelijk dank voor je doorverwijzing! 🎉</p>

<p>We hebben zojuist <strong>{{new_customer_name}}</strong> als nieuwe klant mogen verwelkomen en dat hebben we aan jou te danken.</p>

<p>Het betekent veel voor ons dat je ons aanbeveelt aan vrienden en familie. Jouw vertrouwen in onze service waarderen we enorm!</p>

<p>Als blijk van waardering krijg je binnenkort een kleine attentie van ons.</p>

<p>Nogmaals bedankt en tot snel!</p>

<p>Met vriendelijke groet,<br>
Het {{company_name}} team</p>
            ',
            'is_actief' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('templates')->where('type', 'referral_thank_you')->delete();
    }
};