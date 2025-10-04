<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('uploads', function (Blueprint $table) {
            // Voeg bikefit_id kolom toe als deze nog niet bestaat
            if (!Schema::hasColumn('uploads', 'bikefit_id')) {
                $table->unsignedBigInteger('bikefit_id')->nullable()->after('id');
            }
            
            // Voeg ook andere mogelijke ontbrekende kolommen toe
            if (!Schema::hasColumn('uploads', 'klant_id')) {
                $table->unsignedBigInteger('klant_id')->nullable()->after('bikefit_id');
            }
            
            if (!Schema::hasColumn('uploads', 'path')) {
                $table->string('path')->after('klant_id');
            }
            
            if (!Schema::hasColumn('uploads', 'filename')) {
                $table->string('filename')->nullable()->after('path');
            }
            
            if (!Schema::hasColumn('uploads', 'original_name')) {
                $table->string('original_name')->nullable()->after('filename');
            }
            
            if (!Schema::hasColumn('uploads', 'mime_type')) {
                $table->string('mime_type')->nullable()->after('original_name');
            }
            
            if (!Schema::hasColumn('uploads', 'size')) {
                $table->integer('size')->nullable()->after('mime_type');
            }
        });
        
        // Voeg foreign keys toe (zonder Doctrine check)
        Schema::table('uploads', function (Blueprint $table) {
            try {
                $table->foreign('bikefit_id')->references('id')->on('bikefits')->onDelete('cascade');
            } catch (\Exception $e) {
                // Foreign key mogelijk al aanwezig
            }
            
            try {
                $table->foreign('klant_id')->references('id')->on('klanten')->onDelete('cascade');
            } catch (\Exception $e) {
                // Foreign key mogelijk al aanwezig
            }
        });
    }

    public function down()
    {
        Schema::table('uploads', function (Blueprint $table) {
            // Drop foreign keys eerst
            $table->dropForeign(['bikefit_id']);
            $table->dropForeign(['klant_id']);
            
            // Drop kolommen
            $table->dropColumn([
                'bikefit_id', 'klant_id', 'filename', 
                'original_name', 'mime_type', 'size'
            ]);
        });
    }
};