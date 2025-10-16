{{-- Herbruikbare partial voor AI sectie content --}}
@php
    $lines = explode("\n", $inhoud);
@endphp

@foreach($lines as $line)
    @php
        $line = trim($line);
        if (empty($line)) continue;
        
        $isBulletPoint = str_starts_with($line, '•') || str_starts_with($line, '-') || str_starts_with($line, '*') || str_starts_with($line, '◦');
        $isSubheading = str_contains($line, ':') && strlen($line) < 80 && !$isBulletPoint;
    @endphp
    
    @if($isBulletPoint)
        <div class="ai-bullet">
            {{ ltrim($line, '•-*◦ ') }}
        </div>
    @elseif($isSubheading)
        <div class="ai-subheading">
            {{ $line }}
        </div>
    @else
        <p style="margin: 3px 0;">{{ $line }}</p>
    @endif
@endforeach
