<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Check if table doesn't exist before creating
        if (!Schema::hasTable('invitation_tokens')) {
            Schema::create('invitation_tokens', function (Blueprint $table) {
                $table->id();
                $table->string('email');
                $table->string('token');
                $table->string('type')->default('klant');
                $table->string('temporary_password');
                $table->boolean('used')->default(false);
                $table->timestamp('expires_at');
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('invitation_tokens');
    }
};