<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organisaties', function (Blueprint $table) {
            $table->string('uitnodiging_token', 64)->nullable()->after('notities');
            $table->timestamp('uitnodiging_verstuurd_op')->nullable()->after('uitnodiging_token');
            $table->timestamp('uitnodiging_geaccepteerd_op')->nullable()->after('uitnodiging_verstuurd_op');
        });
    }

    public function down(): void
    {
        Schema::table('organisaties', function (Blueprint $table) {
            $table->dropColumn(['uitnodiging_token', 'uitnodiging_verstuurd_op', 'uitnodiging_geaccepteerd_op']);
        });
    }
};
