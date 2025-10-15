<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Check if table exists before creating
        if (!Schema::hasTable('user_login_logs')) {
            Schema::create('user_login_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->timestamp('login_at');
                $table->timestamp('logout_at')->nullable();
                $table->string('ip_address', 45)->nullable(); // IPv6 support
                $table->integer('session_duration')->nullable(); // in seconds
                $table->string('user_agent')->nullable();
                $table->timestamps();
                
                $table->index(['user_id', 'login_at']);
                $table->index('login_at');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('user_login_logs');
    }
};