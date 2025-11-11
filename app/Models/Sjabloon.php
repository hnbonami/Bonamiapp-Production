<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToOrganisatie;

class Sjabloon extends Model
{
    use HasFactory, BelongsToOrganisatie;

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
        'organisatie_id',
        'user_id',
        'is_actief',
        'is_app_sjabloon'
    ];

    protected $casts = [
        'is_actief' => 'boolean',
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

    public function pages()
    {
        return $this->hasMany(SjabloonPage::class);
    }
}