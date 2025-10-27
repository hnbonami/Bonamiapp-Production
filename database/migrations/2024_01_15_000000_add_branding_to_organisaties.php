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
        Schema::table('organisaties', function (Blueprint $table) {
            // Check en voeg alleen toe als kolom nog niet bestaat
            if (!Schema::hasColumn('organisaties', 'logo_path')) {
                $table->string('logo_path')->nullable()->after('email');
            }
            
            if (!Schema::hasColumn('organisaties', 'favicon_path')) {
                $table->string('favicon_path')->nullable()->after('logo_path');
            }
            
            // Themakleuren (hex codes)
            if (!Schema::hasColumn('organisaties', 'primary_color')) {
                $table->string('primary_color', 7)->default('#3b82f6')->after('favicon_path');
            }
            
            if (!Schema::hasColumn('organisaties', 'secondary_color')) {
                $table->string('secondary_color', 7)->default('#c8e1eb')->after('primary_color');
            }
            
            if (!Schema::hasColumn('organisaties', 'sidebar_color')) {
                $table->string('sidebar_color', 7)->default('#1e293b')->after('secondary_color');
            }
            
            if (!Schema::hasColumn('organisaties', 'text_color')) {
                $table->string('text_color', 7)->default('#111111')->after('sidebar_color');
            }
            
            // Custom CSS voor geavanceerde styling
            if (!Schema::hasColumn('organisaties', 'custom_css')) {
                $table->text('custom_css')->nullable()->after('text_color');
            }
            
            // Branding actief status
            if (!Schema::hasColumn('organisaties', 'branding_enabled')) {
                $table->boolean('branding_enabled')->default(false)->after('custom_css');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organisaties', function (Blueprint $table) {
            $columns = [
                'logo_path',
                'favicon_path',
                'primary_color',
                'secondary_color',
                'sidebar_color',
                'text_color',
                'custom_css',
                'branding_enabled'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('organisaties', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};