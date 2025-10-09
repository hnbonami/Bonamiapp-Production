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
        Schema::create('email_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->enum('subscriber_type', ['klant', 'medewerker'])->default('klant');
            $table->unsignedBigInteger('subscriber_id')->nullable(); // ID van klant of medewerker
            $table->enum('status', ['subscribed', 'unsubscribed'])->default('subscribed');
            $table->string('unsubscribe_token')->unique()->nullable();
            $table->timestamp('subscribed_at')->nullable();
            $table->timestamp('unsubscribed_at')->nullable();
            $table->string('unsubscribe_reason')->nullable();
            $table->timestamps();
            
            $table->index(['email', 'status']);
            $table->index(['subscriber_type', 'subscriber_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_subscriptions');
    }
};