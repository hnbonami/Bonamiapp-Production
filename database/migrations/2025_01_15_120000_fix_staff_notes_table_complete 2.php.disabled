<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('staff_notes', function (Blueprint $table) {
            // Check and add missing columns
            if (!Schema::hasColumn('staff_notes', 'title')) {
                $table->string('title')->after('id');
            }
            if (!Schema::hasColumn('staff_notes', 'content')) {
                $table->text('content')->nullable()->after('title');
            }
            if (!Schema::hasColumn('staff_notes', 'type')) {
                $table->enum('type', ['note', 'announcement', 'task'])->default('note')->after('content');
            }
            if (!Schema::hasColumn('staff_notes', 'tile_size')) {
                $table->enum('tile_size', ['small', 'medium', 'large', 'mini'])->default('medium')->after('type');
            }
            if (!Schema::hasColumn('staff_notes', 'visibility')) {
                $table->enum('visibility', ['all', 'admin', 'private'])->default('all')->after('tile_size');
            }
            if (!Schema::hasColumn('staff_notes', 'priority')) {
                $table->enum('priority', ['low', 'medium', 'high'])->default('medium')->after('visibility');
            }
            if (!Schema::hasColumn('staff_notes', 'background_color')) {
                $table->string('background_color')->default('#ffffff')->after('priority');
            }
            if (!Schema::hasColumn('staff_notes', 'text_color')) {
                $table->string('text_color')->default('#111827')->after('background_color');
            }
            if (!Schema::hasColumn('staff_notes', 'expires_at')) {
                $table->timestamp('expires_at')->nullable()->after('text_color');
            }
            if (!Schema::hasColumn('staff_notes', 'link_url')) {
                $table->string('link_url')->nullable()->after('expires_at');
            }
            if (!Schema::hasColumn('staff_notes', 'link_text')) {
                $table->string('link_text')->nullable()->after('link_url');
            }
            if (!Schema::hasColumn('staff_notes', 'user_id')) {
                $table->unsignedBigInteger('user_id')->after('link_text');
            }
            if (!Schema::hasColumn('staff_notes', 'sort_order')) {
                $table->integer('sort_order')->default(0)->after('user_id');
            }
            if (!Schema::hasColumn('staff_notes', 'published_at')) {
                $table->timestamp('published_at')->nullable()->after('sort_order');
            }
        });
    }

    public function down()
    {
        Schema::table('staff_notes', function (Blueprint $table) {
            $columns = [
                'title', 'content', 'type', 'tile_size', 'visibility', 
                'priority', 'background_color', 'text_color', 'expires_at', 
                'link_url', 'link_text', 'user_id', 'sort_order', 'published_at'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('staff_notes', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};