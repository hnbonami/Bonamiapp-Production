<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('email_template_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('email_trigger_id')->nullable()->constrained()->onDelete('set null');
            $table->string('recipient_email');
            $table->string('recipient_name')->nullable();
            $table->string('subject');
            $table->text('body_html');
            $table->enum('status', ['pending', 'sent', 'failed', 'bounced']);
            $table->string('trigger_type')->nullable(); // manual, automatic, bulk
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('clicked_at')->nullable();
            $table->json('metadata')->nullable(); // Extra data like customer_id, testzadel_id, etc
            $table->timestamps();
            
            $table->index(['recipient_email', 'status']);
            $table->index(['trigger_type', 'sent_at']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_logs');
    }
};