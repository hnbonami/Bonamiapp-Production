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
        // VEILIGHEIDSCHECK: Alleen maken als tabel NIET bestaat
        if (Schema::hasTable('customer_referrals')) {
            \Log::info('✅ customer_referrals table already exists - skipping creation');
            return;
        }

        \Log::info('🔨 Creating customer_referrals table...');
        
        Schema::create('customer_referrals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('referred_customer_id'); // De nieuwe klant
            $table->unsignedBigInteger('referring_customer_id')->nullable(); // De klant die doorverwees (optioneel)
            $table->string('referral_source'); // 'via_internet', 'mond_aan_mond', 'sociale_media', 'andere'
            $table->text('referral_notes')->nullable(); // Extra opmerkingen
            $table->boolean('thank_you_email_sent')->default(false); // Of bedankmail is verstuurd
            $table->timestamp('thank_you_email_sent_at')->nullable(); // Wanneer bedankmail werd verstuurd
            $table->timestamps();

            // Foreign key constraints - VEILIG met cascades
            $table->foreign('referred_customer_id')->references('id')->on('klanten')->onDelete('cascade');
            $table->foreign('referring_customer_id')->references('id')->on('klanten')->onDelete('set null');
            
            // Indexes voor performance
            $table->index(['referring_customer_id']);
            $table->index(['referral_source']);
            $table->index(['thank_you_email_sent']);
        });
        
        \Log::info('✅ customer_referrals table created successfully');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_referrals');
    }
};