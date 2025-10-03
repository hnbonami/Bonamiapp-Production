<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('report_templates', function (Blueprint $table) {
            $table->id();
            $table->string('naam');
            $table->enum('type', ['bikefit', 'inspanningstest'])->default('bikefit');
            $table->text('template_html');
            $table->text('template_css')->nullable();
            $table->boolean('is_actief')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('report_templates');
    }
};