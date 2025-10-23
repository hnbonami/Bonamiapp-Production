<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations - Maak de complete dashboard_content tabel
     */
    public function up(): void
    {
        if (!Schema::hasTable('dashboard_content')) {
            Schema::create('dashboard_content', function (Blueprint $table) {
                $table->id();
                
                // Basis informatie
                $table->string('titel');
                $table->text('inhoud');
                $table->string('type')->default('note'); // note, task, announcement, image, mixed
                
                // Layout & styling
                $table->string('tile_size')->default('medium'); // small, medium, large, banner
                $table->string('background_color')->default('#ffffff');
                $table->string('text_color')->default('#111827');
                
                // Prioriteit & zichtbaarheid
                $table->string('priority')->default('medium'); // low, medium, high, urgent
                $table->string('visibility')->default('all'); // staff, all
                
                // Extra features
                $table->timestamp('expires_at')->nullable();
                $table->boolean('is_pinned')->default(false);
                $table->boolean('is_archived')->default(false);
                $table->integer('sort_order')->default(0);
                
                // Link functionaliteit
                $table->string('link_url', 500)->nullable();
                $table->boolean('open_in_new_tab')->default(false);
                
                // Afbeelding
                $table->string('image_path')->nullable();
                
                // Relaties
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('organisatie_id')->nullable();
                
                $table->timestamps();
                
                // Indexes
                $table->index('organisatie_id');
                $table->index('visibility');
                $table->index('is_archived');
                $table->index(['is_pinned', 'sort_order']);
                
                // Foreign keys
                $table->foreign('created_by')
                      ->references('id')
                      ->on('users')
                      ->onDelete('set null');
                
                if (Schema::hasTable('organisaties')) {
                    $table->foreign('organisatie_id')
                          ->references('id')
                          ->on('organisaties')
                          ->onDelete('cascade');
                }
            });
            
            \Log::info('✅ Dashboard Content tabel aangemaakt');
        }
    }

    /**
     * Reverse the migrations
     */
    public function down(): void
    {
        Schema::dropIfExists('dashboard_content');
    }
};
