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
        // Skip als de tabel al bestaat
        if (Schema::hasTable('email_triggers')) {
            return;
        }
        
        Schema::create('email_triggers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('emails_sent')->default(0);
            $table->timestamp('last_run_at')->nullable();
            $table->json('configuration')->nullable();
            $table->timestamps();
            
            $table->index(['type', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_triggers');
    }
};