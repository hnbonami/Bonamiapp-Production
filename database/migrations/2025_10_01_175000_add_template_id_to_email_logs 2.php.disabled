<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('email_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('email_logs', 'template_id')) {
                $table->unsignedBigInteger('template_id')->nullable()->after('id');
                $table->foreign('template_id')->references('id')->on('email_templates')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('email_logs', function (Blueprint $table) {
            if (Schema::hasColumn('email_logs', 'template_id')) {
                $table->dropForeign(['template_id']);
                $table->dropColumn('template_id');
            }
        });
    }
};