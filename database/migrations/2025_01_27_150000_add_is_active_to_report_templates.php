<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Check if table exists first
        if (!Schema::hasTable('report_templates')) {
            return;
        }
        
        Schema::table('report_templates', function (Blueprint $table) {
            if (!Schema::hasColumn('report_templates', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('id');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('report_templates')) {
            return;
        }
        
        Schema::table('report_templates', function (Blueprint $table) {
            if (Schema::hasColumn('report_templates', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });
    }
};