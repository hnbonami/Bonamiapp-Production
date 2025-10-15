<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Get existing columns first
        $existingColumns = Schema::getColumnListing('testzadels');
        
        // Direct SQL approach to ensure all columns exist
        $columns_to_add = [];
        
        if (!in_array('model', $existingColumns)) {
            $columns_to_add[] = "ALTER TABLE testzadels ADD COLUMN model VARCHAR(255) NOT NULL AFTER merk";
        }
        
        if (!in_array('type', $existingColumns)) {
            $afterColumn = in_array('model', $existingColumns) ? 'model' : 'merk';
            $columns_to_add[] = "ALTER TABLE testzadels ADD COLUMN type VARCHAR(255) NULL AFTER $afterColumn";
        }
        
        if (!in_array('foto_pad', $existingColumns)) {
            $columns_to_add[] = "ALTER TABLE testzadels ADD COLUMN foto_pad VARCHAR(255) NULL AFTER breedte";
        }
        
        if (!in_array('beschrijving', $existingColumns)) {
            if (in_array('status', $existingColumns)) {
                $columns_to_add[] = "ALTER TABLE testzadels ADD COLUMN beschrijving TEXT NULL AFTER status";
            } else {
                $columns_to_add[] = "ALTER TABLE testzadels ADD COLUMN beschrijving TEXT NULL";
            }
        }
        
        if (!in_array('gearchiveerd', $existingColumns)) {
            if (in_array('opmerkingen', $existingColumns)) {
                $columns_to_add[] = "ALTER TABLE testzadels ADD COLUMN gearchiveerd TINYINT(1) NOT NULL DEFAULT 0 AFTER opmerkingen";
            } else {
                $columns_to_add[] = "ALTER TABLE testzadels ADD COLUMN gearchiveerd TINYINT(1) NOT NULL DEFAULT 0";
            }
        }
        
        if (!in_array('gearchiveerd_op', $existingColumns)) {
            $columns_to_add[] = "ALTER TABLE testzadels ADD COLUMN gearchiveerd_op DATETIME NULL AFTER gearchiveerd";
        }
        
        if (!in_array('laatste_herinnering', $existingColumns)) {
            $columns_to_add[] = "ALTER TABLE testzadels ADD COLUMN laatste_herinnering DATETIME NULL AFTER gearchiveerd_op";
        }

        foreach ($columns_to_add as $sql) {
            try {
                DB::statement($sql);
                \Log::info("Successfully added column: " . $sql);
            } catch (\Exception $e) {
                \Log::warning("Failed to add column (might already exist): " . $e->getMessage());
            }
        }
    }

    public function down()
    {
        // Rollback not needed for this fix - this is a one-way migration to fix the structure
    }
};