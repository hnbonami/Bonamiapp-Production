<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('instagram_posts', function (Blueprint $table) {
            $table->id();
            $table->string('titel');
            $table->text('caption')->nullable();
            $table->string('afbeelding')->nullable();
            $table->json('hashtags')->nullable();
            $table->enum('status', ['concept', 'gepubliceerd'])->default('concept');
            $table->timestamp('gepubliceerd_op')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('instagram_posts');
    }
};