<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('invitation_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->string('token')->unique();
            $table->enum('type', ['klant', 'medewerker', 'admin'])->default('klant');
            $table->string('temporary_password')->nullable();
            $table->timestamp('expires_at');
            $table->boolean('is_used')->default(false);
            $table->timestamp('used_at')->nullable();
            $table->unsignedBigInteger('invited_by')->nullable();
            $table->unsignedBigInteger('accepted_by')->nullable();
            $table->json('additional_data')->nullable(); // Voor extra gegevens
            $table->timestamps();
            
            // Indexes
            $table->index(['email', 'type']);
            $table->index(['token', 'expires_at']);
            $table->index(['is_used', 'expires_at']);
            
            // Foreign keys
            $table->foreign('invited_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('accepted_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('invitation_tokens');
    }
};