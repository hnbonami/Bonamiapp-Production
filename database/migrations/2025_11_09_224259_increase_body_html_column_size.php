<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Verhoog body_html kolom van TEXT naar MEDIUMTEXT voor grotere email templates
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE email_templates MODIFY body_html MEDIUMTEXT');
    }

    /**
     * Rollback: verander terug naar TEXT
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE email_templates MODIFY body_html TEXT');
    }
};
