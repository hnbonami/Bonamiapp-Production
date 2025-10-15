<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('email_logs')) {
            Schema::create('email_logs', function (Blueprint $table) {
                $table->id();
                $table->string('recipient_email');
                $table->string('subject');
                $table->unsignedBigInteger('template_id')->nullable();
                $table->string('trigger_name')->nullable();
                $table->enum('status', ['sent', 'failed', 'pending'])->default('pending');
                $table->timestamp('sent_at')->nullable();
                $table->text('error_message')->nullable();
                $table->json('variables')->nullable();
                $table->timestamps();
                
                $table->foreign('template_id')->references('id')->on('email_templates')->onDelete('set null');
                $table->index(['status', 'sent_at']);
                $table->index('trigger_name');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('email_logs');
    }
};