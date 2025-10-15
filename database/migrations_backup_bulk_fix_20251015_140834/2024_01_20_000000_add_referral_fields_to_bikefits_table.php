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
        Schema::table('bikefits', function (Blueprint $table) {
            // Check if columns don't already exist
            if (!Schema::hasColumn('bikefits', 'doorverwijzing_type')) {
                $table->string('doorverwijzing_type')->nullable()->after('schoenmaat');
            }
            if (!Schema::hasColumn('bikefits', 'doorverwijzing_klant_id')) {
                $table->unsignedBigInteger('doorverwijzing_klant_id')->nullable()->after('doorverwijzing_type');
            }
            if (!Schema::hasColumn('bikefits', 'doorverwijzing_processed')) {
                $table->boolean('doorverwijzing_processed')->default(false)->after('doorverwijzing_klant_id');
            }
        });
        
        // Add foreign key constraint if it doesn't exist
        try {
            Schema::table('bikefits', function (Blueprint $table) {
                $table->foreign('doorverwijzing_klant_id')->references('id')->on('klanten')->onDelete('set null');
            });
        } catch (\Exception $e) {
            // Foreign key might already exist, ignore
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bikefits', function (Blueprint $table) {
            $table->dropForeign(['doorverwijzing_klant_id']);
            $table->dropColumn(['doorverwijzing_type', 'doorverwijzing_klant_id', 'doorverwijzing_processed']);
        });
    }
};