<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dashboard_widgets', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // 'chart', 'text', 'image', 'button', 'metric'
            $table->string('title')->nullable();
            $table->text('content')->nullable();
            $table->string('chart_type')->nullable();
            $table->json('chart_data')->nullable();
            $table->string('image_path')->nullable();
            $table->string('button_text')->nullable();
            $table->string('button_url')->nullable();
            $table->string('background_color')->default('#ffffff');
            $table->string('text_color')->default('#000000');
            $table->integer('grid_x')->default(0);
            $table->integer('grid_y')->default(0);
            $table->integer('grid_width')->default(4);
            $table->integer('grid_height')->default(3);
            $table->enum('visibility', ['everyone', 'medewerkers', 'only_me'])->default('everyone');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('cascade');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('dashboard_user_layouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('widget_id')->constrained('dashboard_widgets')->onDelete('cascade');
            $table->integer('grid_x');
            $table->integer('grid_y');
            $table->integer('grid_width');
            $table->integer('grid_height');
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
            $table->unique(['user_id', 'widget_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dashboard_user_layouts');
        Schema::dropIfExists('dashboard_widgets');
    }
};