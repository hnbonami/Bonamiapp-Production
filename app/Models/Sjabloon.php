<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sjabloon extends Model
{
    use HasFactory;

    protected $table = 'sjablonen';

    // Add route key name for proper binding
    public function getRouteKeyName()
    {
        return 'id';
    }

    protected $fillable = [
        'naam',
        'categorie',
        'testtype',
        'beschrijving',
        'user_id'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function paginas()
    {
        return $this->hasMany(SjabloonPagina::class)->orderBy('pagina_nummer');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}