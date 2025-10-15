<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('templates', function (Blueprint $table) {
            // Change ENUM to varchar to allow any value
            $table->string('type', 100)->change();
        });
    }

    public function down()
    {
        Schema::table('templates', function (Blueprint $table) {
            $table->enum('type', ['email','rapport','brief','bikefit','inspanningstest','algemeen'])->change();
        });
    }
};