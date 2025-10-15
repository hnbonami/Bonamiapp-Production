<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Voer de migratie uit voor email_templates tabel
     */
    public function up(): void
    {
        // Controleer of tabel al bestaat voordat we deze aanmaken
        if (!Schema::hasTable('email_templates')) {
            Schema::create('email_templates', function (Blueprint $table) {
                $table->id();
                $table->string('template_key')->nullable()->unique();
                $table->string('name');
                $table->string('type');
                $table->string('subject');
                $table->longText('body_html');
                $table->longText('content')->nullable(); // Voor nieuwe email systeem
                $table->text('body_text')->nullable();
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
                
                $table->index(['type', 'is_active']);
                $table->index('template_key');
            });
        } else {
            // Voeg ontbrekende kolommen toe aan bestaande tabel
            Schema::table('email_templates', function (Blueprint $table) {
                if (!Schema::hasColumn('email_templates', 'template_key')) {
                    $table->string('template_key')->nullable()->unique()->after('id');
                }
                if (!Schema::hasColumn('email_templates', 'content')) {
                    $table->longText('content')->nullable()->after('body_html');
                }
                if (!Schema::hasColumn('email_templates', 'created_by')) {
                    $table->unsignedBigInteger('created_by')->nullable()->after('is_active');
                }
            });
        }
    }

    /**
     * Draai de migratie terug
     */
    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }
};