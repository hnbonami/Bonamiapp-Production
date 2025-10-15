<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_triggers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type'); // testzadel_reminder, birthday, welcome_customer, etc
            $table->foreignId('email_template_id')->constrained()->onDelete('cascade');
            $table->boolean('is_active')->default(true);
            $table->json('conditions'); // When to trigger (days, events, etc)
            $table->json('settings')->nullable(); // Additional settings
            $table->timestamp('last_run_at')->nullable();
            $table->integer('emails_sent')->default(0);
            $table->timestamps();
            
            $table->index(['type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_triggers');
    }
};