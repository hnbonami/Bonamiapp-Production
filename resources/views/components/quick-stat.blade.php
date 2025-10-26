@props(['title', 'value', 'trend' => null, 'trendUp' => true, 'sparklineData' => [], 'color' => '#c8e1eb'])

<div class="quick-stat-widget" style="display:flex;flex-direction:column;height:100%;justify-content:space-between;">
    <!-- Header met titel en waarde -->
    <div>
        <div style="font-size:0.85em;opacity:0.7;margin-bottom:0.5em;">{{ $title }}</div>
        <div style="font-size:2.5em;font-weight:700;line-height:1;margin-bottom:0.3em;">
            {{ $value }}
        </div>
        
        @if($trend !== null)
        <div style="display:flex;align-items:center;gap:0.5em;font-size:0.85em;">
            @if($trendUp)
                <svg width="16" height="16" fill="none" stroke="#10b981" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
                <span style="color:#10b981;font-weight:600;">+{{ $trend }}%</span>
            @else
                <svg width="16" height="16" fill="none" stroke="#ef4444" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                </svg>
                <span style="color:#ef4444;font-weight:600;">{{ $trend }}%</span>
            @endif
            <span style="opacity:0.6;">vs vorige maand</span>
        </div>
        @endif
    </div>
    
    <!-- Mini Sparkline Chart -->
    @if(count($sparklineData) > 0)
    <div style="margin-top:auto;padding-top:1em;">
        <canvas class="sparkline-chart" 
                data-values="{{ json_encode($sparklineData) }}" 
                data-color="{{ $color }}" 
                width="200" 
                height="40"
                style="width:100%;height:40px;"></canvas>
    </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sparklines = document.querySelectorAll('.sparkline-chart');
    
    sparklines.forEach(canvas => {
        const data = JSON.parse(canvas.dataset.values);
        const color = canvas.dataset.color;
        
        drawSparkline(canvas, data, color);
    });
});

function drawSparkline(canvas, data, color) {
    const ctx = canvas.getContext('2d');
    const width = canvas.width;
    const height = canvas.height;
    const padding = 5;
    
    // Clear canvas
    ctx.clearRect(0, 0, width, height);
    
    // Bereken min/max voor scaling
    const min = Math.min(...data);
    const max = Math.max(...data);
    const range = max - min || 1;
    
    // Bereken punten
    const stepX = (width - padding * 2) / (data.length - 1);
    const points = data.map((value, index) => {
        const x = padding + index * stepX;
        const y = height - padding - ((value - min) / range) * (height - padding * 2);
        return { x, y };
    });
    
    // Teken lijn
    ctx.beginPath();
    ctx.strokeStyle = color;
    ctx.lineWidth = 2;
    ctx.lineJoin = 'round';
    ctx.lineCap = 'round';
    
    points.forEach((point, index) => {
        if (index === 0) {
            ctx.moveTo(point.x, point.y);
        } else {
            ctx.lineTo(point.x, point.y);
        }
    });
    
    ctx.stroke();
    
    // Teken gradient fill onder de lijn
    const gradient = ctx.createLinearGradient(0, 0, 0, height);
    gradient.addColorStop(0, color + '40'); // 25% opacity
    gradient.addColorStop(1, color + '00'); // 0% opacity
    
    ctx.lineTo(points[points.length - 1].x, height);
    ctx.lineTo(points[0].x, height);
    ctx.closePath();
    ctx.fillStyle = gradient;
    ctx.fill();
    
    // Teken punten
    ctx.fillStyle = color;
    points.forEach(point => {
        ctx.beginPath();
        ctx.arc(point.x, point.y, 3, 0, Math.PI * 2);
        ctx.fill();
    });
}
</script>