<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Check if table exists before creating
        if (!Schema::hasTable('role_test_permissions')) {
            Schema::create('role_test_permissions', function (Blueprint $table) {
                $table->id();
                $table->string('role_name'); // admin, medewerker, klant
                $table->string('test_type'); // bikefit, inspanningstest, voedingsadvies, etc.
                $table->boolean('can_access')->default(false);
                $table->boolean('can_create')->default(false);
                $table->boolean('can_edit')->default(false);
                $table->timestamps();
                
                $table->unique(['role_name', 'test_type']);
                $table->index(['role_name', 'can_access']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('role_test_permissions');
    }
};