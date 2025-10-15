<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('staff_invitations', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->string('voornaam');
            $table->string('achternaam');
            $table->string('token')->unique();
            $table->enum('role', ['admin', 'medewerker', 'fysiotherapeut', 'trainer'])->default('medewerker');
            $table->string('temporary_password')->nullable();
            $table->timestamp('expires_at');
            $table->boolean('is_accepted')->default(false);
            $table->timestamp('accepted_at')->nullable();
            $table->unsignedBigInteger('invited_by');
            $table->unsignedBigInteger('user_id')->nullable(); // ID van aangemaakte user
            $table->json('permissions')->nullable(); // Specifieke rechten
            $table->text('welcome_message')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['email', 'role']);
            $table->index(['token', 'expires_at']);
            $table->index(['is_accepted', 'expires_at']);
            
            // Foreign keys
            $table->foreign('invited_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('staff_invitations');
    }
};