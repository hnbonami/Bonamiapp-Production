<?php

namespace App\Http\Middleware;

use App\Models\Bikefit;
use App\Services\ReportTemplateRenderer;
use App\Services\BikefitCalculator;
use Closure;
use Illuminate\Http\Request;

class ReplaceReportPlaceholders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        try {
            $contentType = $response->headers->get('Content-Type', 'text/html');
            if ($contentType && stripos($contentType, 'text/html') === false && stripos($contentType, 'application/xhtml') === false) {
                return $response; // only HTML
            }

            $html = (string) $response->getContent();

            // Detect if any of the placeholder tokens are present; if not, bail
            $keys = ['mobiliteitstabel','mobility_table','MobiliteitTabel','Mobiliteitstabel','mobiliteitsstabel'];
            $pattern = '/(\{\{\s*(?:'.implode('|',$keys).')\s*\}\}|\[\[\s*(?:'.implode('|',$keys).')\s*\]\]|::\s*(?:'.implode('|',$keys).')\s*::|\$\s*(?:'.implode('|',$keys).')\s*\$)/i';
            if (!preg_match($pattern, $html)) {
                return $response; // nothing to replace
            }

            // Resolve Bikefit from route parameters or path
            $bikefit = null;
            $route = $request->route();
            if ($route) {
                $params = method_exists($route, 'parameters') ? $route->parameters() : [];
                foreach ($params as $name => $val) {
                    if ($val instanceof Bikefit) { $bikefit = $val; break; }
                    if (is_numeric($val) && stripos((string)$name, 'bikefit') !== false) { $bikefit = Bikefit::find((int)$val); break; }
                }
            }
            if (!$bikefit && preg_match('/\/bikefit\/(\d+)/i', $request->path(), $m)) {
                $bikefit = Bikefit::find((int) $m[1]);
            }

            if ($bikefit) {
                $renderer = app(ReportTemplateRenderer::class);
                $calculator = app(BikefitCalculator::class);
                $mobilityHtml = (string) $calculator->renderMobilityTableHtml($bikefit);

                // Server-side replacement
                $html = $renderer->render($html, ['bikefit' => $bikefit, 'mobility_table' => $mobilityHtml]);

                // Debug marker to verify middleware ran
                $marker = "\n<!-- mobility-placeholder-replaced: bikefit=".(int)$bikefit->id." -->\n";
                if (stripos($html, '</body>') !== false) {
                    $html = str_ireplace('</body>', $marker.'</body>', $html);
                } else {
                    $html .= $marker;
                }

                $response->setContent($html);
            }
        } catch (\Throwable $e) {
            // silent fail
        }

        return $response;
    }
}
