<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('templates')) {
            Schema::create('templates', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('type');
                $table->json('html_contents')->nullable();
                $table->json('background_images')->nullable();
                $table->timestamps();
            });
        } else {
            Schema::table('templates', function (Blueprint $table) {
                if (!Schema::hasColumn('templates', 'name')) {
                    $table->string('name')->after('id');
                }
                if (!Schema::hasColumn('templates', 'type')) {
                    $table->string('type')->after('name');
                }
                if (!Schema::hasColumn('templates', 'html_contents')) {
                    $table->json('html_contents')->nullable()->after('type');
                }
                if (!Schema::hasColumn('templates', 'background_images')) {
                    $table->json('background_images')->nullable()->after('html_contents');
                }
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('templates');
    }
};