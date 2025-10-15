<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Fix klanten table niveau enum
        DB::statement('ALTER TABLE klanten MODIFY COLUMN niveau ENUM("recreatief", "competitie", "elite") NULL');
        
        // Create newsletters table if not exists
        if (!Schema::hasTable('newsletters')) {
            Schema::create('newsletters', function (Blueprint $table) {
                $table->id();
                $table->string('titel');
                $table->text('inhoud')->nullable();
                $table->enum('status', ['concept', 'verzonden'])->default('concept');
                $table->timestamp('verzonden_op')->nullable();
                $table->timestamps();
            });
        }
        
        // Ensure staff_notes has is_new column
        if (!Schema::hasColumn('staff_notes', 'is_new')) {
            Schema::table('staff_notes', function (Blueprint $table) {
                $table->boolean('is_new')->default(true)->after('status');
            });
        }
        
        // Create any other missing tables that might be referenced
        $tables = [
            'user_uploads' => function (Blueprint $table) {
                $table->id();
                $table->string('filename');
                $table->string('original_name');
                $table->string('mime_type');
                $table->integer('file_size');
                $table->string('path');
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->timestamps();
            },
            'background_pdfs' => function (Blueprint $table) {
                $table->id();
                $table->string('filename');
                $table->string('path');
                $table->timestamps();
            },
        ];
        
        foreach ($tables as $tableName => $callback) {
            if (!Schema::hasTable($tableName)) {
                Schema::create($tableName, $callback);
            }
        }
    }

    public function down()
    {
        // Revert changes if needed
        $tables = ['newsletters', 'user_uploads', 'background_pdfs'];
        foreach ($tables as $table) {
            Schema::dropIfExists($table);
        }
    }
};