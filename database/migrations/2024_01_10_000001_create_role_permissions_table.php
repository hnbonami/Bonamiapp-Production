<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Check if table exists before creating
        if (!Schema::hasTable('role_permissions')) {
            Schema::create('role_permissions', function (Blueprint $table) {
                $table->id();
                $table->string('role_name'); // admin, medewerker, klant
                $table->string('tab_name'); // dashboard, klanten, medewerkers, etc.
                $table->boolean('can_access')->default(false);
                $table->timestamps();
                
                $table->unique(['role_name', 'tab_name']);
                $table->index(['role_name', 'can_access']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('role_permissions');
    }
};