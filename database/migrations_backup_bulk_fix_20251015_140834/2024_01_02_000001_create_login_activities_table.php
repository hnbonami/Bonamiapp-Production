<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('login_activities')) {
            Schema::create('login_activities', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('ip_address');
                $table->text('user_agent');
                $table->string('device')->nullable();
                $table->string('browser')->nullable();
                $table->string('platform')->nullable();
                $table->string('location')->nullable();
                $table->enum('status', ['success', 'failed'])->default('success');
                $table->timestamp('logged_in_at');
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->index(['user_id', 'logged_in_at']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('login_activities');
    }
};