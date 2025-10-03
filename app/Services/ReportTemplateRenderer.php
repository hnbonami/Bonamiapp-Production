<?php
namespace App\Services;

use App\Models\ReportTemplate;

class ReportTemplateRenderer
{
    /**
     * Render a template (ReportTemplate instance or layout array) for a given bikefit.
     * Accepts either a ReportTemplate model or a decoded layout array or a JSON string.
     */
    public function renderTemplateForBikefit($templateOrLayout, $bikefit): string
    {
        $layout = [];
        if (is_string($templateOrLayout)) {
            try { $layout = json_decode($templateOrLayout, true) ?: []; } catch (\Throwable $e) { $layout = []; }
        } elseif (is_array($templateOrLayout)) {
            $layout = $templateOrLayout;
        } elseif ($templateOrLayout instanceof ReportTemplate) {
            try { $layout = json_decode($templateOrLayout->json_layout ?? '[]', true) ?: []; } catch (\Throwable $e) { $layout = []; }
        } else {
            // unknown type, try to coerce
            $layout = [];
        }

        $html = '';
        foreach ($layout as $block) {
            $type = $block['type'] ?? 'text';
            $partial = 'report_templates.blocks.' . $type;
            try {
                $html .= view($partial, ['block' => $block, 'bikefit' => $bikefit])->render();
            } catch (\Throwable $e) {
                $html .= '<div class="block">' . e($type) . '</div>';
            }
        }
        return $html;
    }
}
