@php
    /** @var array $block */
    /** @var \App\Models\Bikefit $bikefit */
    $content = $block['html'] ?? $block['content'] ?? $block['text'] ?? '';
    $renderer = app(\App\Services\ReportTemplateRenderer::class);
    $processed = $renderer->render($content, ['bikefit' => $bikefit]);
@endphp
<div class="block block-html">{!! $processed !!}</div>
