<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CheckStuurpenColumns extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bikefit:check-stuurpen';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check if stuurpen columns exist in bikefits table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking stuurpen columns in bikefits table...');
        
        $columns = ['aanpassingen_stuurpen_aan', 'aanpassingen_stuurpen_pre', 'aanpassingen_stuurpen_post'];
        
        foreach ($columns as $column) {
            $exists = Schema::hasColumn('bikefits', $column);
            if ($exists) {
                $this->info("✓ Column '{$column}' exists");
            } else {
                $this->error("✗ Column '{$column}' does NOT exist");
            }
        }
        
        // Also show all columns in bikefits table
        $this->info("\nAll columns in bikefits table:");
        $allColumns = Schema::getColumnListing('bikefits');
        foreach ($allColumns as $col) {
            $this->line("  - {$col}");
        }
        
        // If columns don't exist, try to add them
        $missingColumns = array_filter($columns, function($col) {
            return !Schema::hasColumn('bikefits', $col);
        });
        
        if (!empty($missingColumns)) {
            $this->warn("\nMissing columns detected. Adding them now...");
            
            try {
                Schema::table('bikefits', function ($table) use ($missingColumns) {
                    if (in_array('aanpassingen_stuurpen_aan', $missingColumns)) {
                        $table->boolean('aanpassingen_stuurpen_aan')->default(0)->after('aanpassingen_drop');
                        $this->info("Added: aanpassingen_stuurpen_aan");
                    }
                    if (in_array('aanpassingen_stuurpen_pre', $missingColumns)) {
                        $table->decimal('aanpassingen_stuurpen_pre', 8, 2)->nullable()->after('aanpassingen_stuurpen_aan');
                        $this->info("Added: aanpassingen_stuurpen_pre");
                    }
                    if (in_array('aanpassingen_stuurpen_post', $missingColumns)) {
                        $table->decimal('aanpassingen_stuurpen_post', 8, 2)->nullable()->after('aanpassingen_stuurpen_pre');
                        $this->info("Added: aanpassingen_stuurpen_post");
                    }
                });
                
                $this->info("\n✓ All missing columns have been added successfully!");
                
            } catch (\Exception $e) {
                $this->error("Failed to add columns: " . $e->getMessage());
            }
        } else {
            $this->info("\n✓ All stuurpen columns exist!");
        }
    }
}