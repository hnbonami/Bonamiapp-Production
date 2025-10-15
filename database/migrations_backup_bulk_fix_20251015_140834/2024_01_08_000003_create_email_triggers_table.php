<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Create email_logs table instead of conflicting with email_triggers
        if (!Schema::hasTable('email_logs')) {
            Schema::create('email_logs', function (Blueprint $table) {
                $table->id();
                $table->string('trigger_name');
                $table->unsignedBigInteger('template_id')->nullable();
                $table->string('recipient_email');
                $table->json('variables')->nullable();
                $table->timestamp('sent_at');
                $table->string('status')->default('sent');
                $table->timestamps();
                
                // Only add foreign key if email_templates table exists
                if (Schema::hasTable('email_templates')) {
                    $table->foreign('template_id')->references('id')->on('email_templates')->onDelete('set null');
                }
                $table->index(['trigger_name', 'sent_at']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('email_logs');
    }
};