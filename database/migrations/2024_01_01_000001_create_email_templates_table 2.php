<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Deze migratie is al uitgevoerd - email_templates tabel bestaat al.
     */
    public function up(): void
    {
        // Skip - tabel bestaat al
        if (Schema::hasTable('email_templates')) {
            \Log::info('email_templates tabel bestaat al - migratie overgeslagen');
            return;
        }

        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type'); // testzadel_reminder, welcome_customer, birthday, etc
            $table->string('subject');
            $table->text('body_html');
            $table->text('body_text')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('type');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Niets te doen
    }
};