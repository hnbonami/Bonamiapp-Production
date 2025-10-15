<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Direct SQL gebruiken omdat Doctrine niet beschikbaar is
        DB::statement('ALTER TABLE staff_notes MODIFY COLUMN type VARCHAR(50)');
    }

    public function down()
    {
        DB::statement('ALTER TABLE staff_notes MODIFY COLUMN type VARCHAR(20)');
    }
};