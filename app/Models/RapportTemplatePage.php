<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * Pagina binnen een Rapport Template
 * 
 * Elke pagina heeft:
 * - Eigen foto/media
 * - Layout configuratie
 * - Custom content
 */
class RapportTemplatePage extends Model
{
    protected $fillable = [
        'template_id',
        'page_number',
        'page_type',
        'page_title',
        'media_path',
        'media_type',
        'media_position',
        'media_size',
        'media_settings',
        'show_logo',
        'custom_header',
        'custom_footer',
        'content_settings',
        'layout_type',
        'layout_config',
    ];

    protected $casts = [
        'media_settings' => 'array',
        'content_settings' => 'array',
        'layout_config' => 'array',
        'show_logo' => 'boolean',
        'media_size' => 'integer',
        'page_number' => 'integer',
    ];

    /**
     * Template waar deze pagina bij hoort
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(RapportTemplate::class);
    }

    /**
     * Page type definitie
     */
    public function pageType(): BelongsTo
    {
        return $this->belongsTo(RapportPageType::class, 'page_type', 'type_key');
    }

    /**
     * Accessor: Haal media URL op (voor web weergave)
     */
    public function getMediaUrlAttribute(): ?string
    {
        if ($this->media_path) {
            return Storage::url($this->media_path);
        }
        return null;
    }

    /**
     * Accessor: Haal volledige file path op (voor PDF generatie)
     */
    public function getMediaFullPathAttribute(): ?string
    {
        if ($this->media_path) {
            return Storage::path($this->media_path);
        }
        return null;
    }

    /**
     * Helper: Check of deze pagina media heeft
     */
    public function hasMedia(): bool
    {
        return !empty($this->media_path) && $this->media_type !== 'none';
    }

    /**
     * Helper: Haal media opacity op
     */
    public function getMediaOpacity(): float
    {
        return $this->media_settings['opacity'] ?? 1.0;
    }

    /**
     * Helper: Haal media filter op
     */
    public function getMediaFilter(): string
    {
        return $this->media_settings['filter'] ?? 'none';
    }

    /**
     * Helper: Check of pagina custom header heeft
     */
    public function hasCustomHeader(): bool
    {
        return !empty($this->custom_header);
    }

    /**
     * Helper: Check of pagina custom footer heeft
     */
    public function hasCustomFooter(): bool
    {
        return !empty($this->custom_footer);
    }

    /**
     * Helper: Haal blade partial path op
     */
    public function getBladePartialPath(): string
    {
        $pageType = $this->pageType;
        
        if ($pageType && $pageType->blade_partial) {
            return $pageType->blade_partial;
        }
        
        // Fallback naar convention-based partial
        $rapportType = $this->template->rapport_type;
        $type = str_replace($rapportType . '_', '', $this->page_type);
        
        return "{$rapportType}.report.partials._{$type}";
    }

    /**
     * Haal vaste media instellingen op basis van pagina type
     * Deze settings zijn VAST en kunnen niet door gebruiker gewijzigd worden
     */
    public static function getDefaultMediaSettings(string $pageType): array
    {
        $settings = [
            // Inspanningstest pagina's
            'inspanningstest_cover' => [
                'position' => 'background',
                'size' => 100,
                'opacity' => 0.3,
            ],
            'inspanningstest_algemeen' => [
                'position' => 'top',
                'size' => 40,
                'opacity' => 1.0,
            ],
            'inspanningstest_trainingstatus' => [
                'position' => 'right',
                'size' => 30,
                'opacity' => 1.0,
            ],
            'inspanningstest_testresultaten' => [
                'position' => 'top',
                'size' => 30,
                'opacity' => 1.0,
            ],
            'inspanningstest_grafiek' => [
                'position' => 'top',
                'size' => 25,
                'opacity' => 1.0,
            ],
            'inspanningstest_drempelwaarden' => [
                'position' => 'right',
                'size' => 35,
                'opacity' => 1.0,
            ],
            'inspanningstest_zones' => [
                'position' => 'top',
                'size' => 30,
                'opacity' => 1.0,
            ],
            'inspanningstest_ai_analyse' => [
                'position' => 'left',
                'size' => 25,
                'opacity' => 1.0,
            ],
            
            // Bikefit pagina's (toekomst)
            'bikefit_cover' => [
                'position' => 'background',
                'size' => 100,
                'opacity' => 0.3,
            ],
            'bikefit_algemeen' => [
                'position' => 'top',
                'size' => 40,
                'opacity' => 1.0,
            ],
        ];

        return $settings[$pageType] ?? [
            'position' => 'top',
            'size' => 40,
            'opacity' => 1.0,
        ];
    }

    /**
     * Scope: Order by page number
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('page_number');
    }
}
