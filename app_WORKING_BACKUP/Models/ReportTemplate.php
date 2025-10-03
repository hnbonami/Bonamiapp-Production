<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportTemplate extends Model
{
    protected $fillable = ['name', 'slug', 'kind', 'json_layout', 'is_active', 'created_by'];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}

// helper kinds
if (!method_exists(ReportTemplate::class, 'kinds')) {
    class_alias(ReportTemplate::class, 'RTAlias_helper_for_kinds');
}

// Provide a global helper function so Blade views (and other global code) can call it.
// helpers moved to app/Support/helpers.php and autoloaded via composer.json
