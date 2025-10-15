<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_settings', function (Blueprint $table) {
            $table->id();
            $table->string('company_name')->default('Bonami Cycling');
            $table->string('logo_path')->nullable();
            $table->string('primary_color')->default('#667eea');
            $table->string('secondary_color')->default('#764ba2');
            $table->string('footer_text')->nullable();
            $table->text('signature')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_settings');
    }
};