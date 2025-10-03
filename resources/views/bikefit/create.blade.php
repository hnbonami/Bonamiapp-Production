@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex justify-between items-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Nieuwe bikefit voor {{ $klant->voornaam }} {{ $klant->naam }}</h1>
    </div>

    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
        <div class="p-6">
            @if ($errors->any())
                <div class="mb-4 text-red-600">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form method="POST" action="{{ route('bikefit.store', ['klant' => $klant->id]) }}">
                @csrf
                
                @include('bikefit._form', ['submitLabel' => 'Bikefit aanmaken', 'isEdit' => false])

                <!-- Nieuw Uitleensysteem Component -->
                @include('components.bikefit-uitleensysteem')

                <div class="flex justify-between mt-8">
                    <div class="flex gap-2">
                        <a href="{{ route('klanten.show', $klant->id) }}" class="px-6 py-3 bg-gray-300 text-black rounded font-semibold hover:bg-gray-400">
                            Terug naar klant
                        </a>
                        <button type="submit" name="save_and_results" value="1" class="px-6 py-3 bg-blue-600 text-white rounded font-semibold hover:bg-blue-700">
                            Opslaan
                        </button>
                    </div>
                    @if(isset($bikefit) && $bikefit)
                        <a href="{{ route('bikefit.results', ['klant' => $klant->id, 'bikefit' => $bikefit->id]) }}" class="px-6 py-3 bg-green-600 text-white rounded font-semibold hover:bg-green-700">
                            Bereken resultaten
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Beenlengteverschil toggle functionaliteit
    const beenlengteSelect = document.querySelector('select[name="beenlengteverschil"]');
    const beenlengteCmField = document.querySelector('[name="beenlengteverschil_cm"]');
    
    if (beenlengteSelect && beenlengteCmField) {
        const cmFieldContainer = beenlengteCmField.closest('.mb-4') || beenlengteCmField.closest('.form-group') || beenlengteCmField.parentElement;
        
        function toggleBeenlengteCmField() {
            if (beenlengteSelect.value === '1') {
                cmFieldContainer.style.display = 'block';
            } else {
                cmFieldContainer.style.display = 'none';
            }
        }
        
        beenlengteSelect.addEventListener('change', toggleBeenlengteCmField);
        toggleBeenlengteCmField(); // Initial state
    }
    
    // Steunzolen toggle functionaliteit
    const steunzolenSelect = document.querySelector('select[name="steunzolen"]');
    const steunzolenRedenField = document.querySelector('[name="steunzolen_reden"]');
    
    if (steunzolenSelect && steunzolenRedenField) {
        const redenFieldContainer = steunzolenRedenField.closest('.mb-4') || steunzolenRedenField.closest('.form-group') || steunzolenRedenField.parentElement;
        
        function toggleSteunzolenRedenField() {
            if (steunzolenSelect.value === '1') {
                redenFieldContainer.style.display = 'block';
            } else {
                redenFieldContainer.style.display = 'none';
            }
        }
        
        steunzolenSelect.addEventListener('change', toggleSteunzolenRedenField);
        toggleSteunzolenRedenField(); // Initial state
    }
    
    console.log('Drempel inputs gevonden:', aerobeDrempelInput, anaerobeDrempelInput);
    
    if (aerobeDrempelInput && anaerobeDrempelInput) {
        // Eerst kijken of er al een canvas is, anders maken we er een
        let ctx = document.getElementById('hartslagChart');
        if (!ctx) {
            // Maak een nieuwe canvas aan en voeg toe na de drempel inputs
            const chartContainer = document.createElement('div');
            chartContainer.className = 'mb-8 mt-8';
            chartContainer.innerHTML = `
                <h3 class="text-lg font-medium text-gray-900 mb-4">Hartslag Zones</h3>
                <div style="height: 400px;">
                    <canvas id="hartslagChart"></canvas>
                </div>
            `;
            anaerobeDrempelInput.closest('.mb-4').after(chartContainer);
            ctx = document.getElementById('hartslagChart');
        }
        
        if (ctx) {
            console.log('Canvas gevonden:', ctx);
            
            // Check of Chart.js beschikbaar is
            if (typeof Chart === 'undefined') {
                console.error('Chart.js is niet geladen!');
                ctx.parentElement.innerHTML = '<p class="text-red-600">Chart.js is niet geladen. Voeg Chart.js toe aan je layout.</p>';
                return;
            }
            
            // Plugin voor versleepbare drempellijnen
            Chart.register({
                id: 'draggableLines',
                afterDraw: function(chart) {
                    const ctx = chart.ctx;
                    const chartArea = chart.chartArea;
                    
                    // Aërobe drempel (rode lijn)
                    const aerobicValue = parseInt(aerobeDrempelInput.value) || 140;
                    const aerobicY = chart.scales.y.getPixelForValue(aerobicValue);
                    
                    ctx.save();
                    ctx.strokeStyle = 'red';
                    ctx.lineWidth = 2;
                    ctx.setLineDash([5, 5]);
                    ctx.beginPath();
                    ctx.moveTo(chartArea.left, aerobicY);
                    ctx.lineTo(chartArea.right, aerobicY);
                    ctx.stroke();
                    
                    // Draggable indicator voor aërobe drempel
                    ctx.fillStyle = 'red';
                    ctx.fillRect(chartArea.left - 5, aerobicY - 3, 10, 6);
                    
                    // Anaërobe drempel (oranje lijn)
                    const anaerobicValue = parseInt(anaerobeDrempelInput.value) || 160;
                    const anaerobicY = chart.scales.y.getPixelForValue(anaerobicValue);
                    
                    ctx.strokeStyle = 'orange';
                    ctx.beginPath();
                    ctx.moveTo(chartArea.left, anaerobicY);
                    ctx.lineTo(chartArea.right, anaerobicY);
                    ctx.stroke();
                    
                    // Draggable indicator voor anaërobe drempel
                    ctx.fillStyle = 'orange';
                    ctx.fillRect(chartArea.left - 5, anaerobicY - 3, 10, 6);
                    
                    ctx.restore();
                }
            });

            // Chart aanmaken
            const hartslagChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: [0, 50, 100, 150, 200, 250, 300],
                    datasets: [{
                        label: 'Hartslag (bpm)',
                        data: [120, 135, 145, 155, 165, 170, 175],
                        borderColor: 'blue',
                        backgroundColor: 'rgba(0, 123, 255, 0.1)',
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'Tijd (minuten)'
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'Hartslag (bpm)'
                            },
                            min: 60,
                            max: 200
                        }
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        },
                        title: {
                            display: true,
                            text: 'Hartslag Zones'
                        }
                    }
                }
            });
            
            // Mouse event variabelen
            let isDragging = false;
            let dragTarget = null;
            
            // Mouse event listeners
            hartslagChart.canvas.addEventListener('mousedown', function(e) {
                const rect = hartslagChart.canvas.getBoundingClientRect();
                const y = e.clientY - rect.top;
                
                const aerobicValue = parseInt(aerobeDrempelInput.value) || 140;
                const anaerobicValue = parseInt(anaerobeDrempelInput.value) || 160;
                
                const aerobicY = hartslagChart.scales.y.getPixelForValue(aerobicValue);
                const anaerobicY = hartslagChart.scales.y.getPixelForValue(anaerobicValue);
                
                if (Math.abs(y - aerobicY) < 15) {
                    isDragging = true;
                    dragTarget = 'aerobic';
                    e.preventDefault();
                } else if (Math.abs(y - anaerobicY) < 15) {
                    isDragging = true;
                    dragTarget = 'anaerobic';
                    e.preventDefault();
                }
            });
            
            hartslagChart.canvas.addEventListener('mousemove', function(e) {
                const rect = hartslagChart.canvas.getBoundingClientRect();
                const y = e.clientY - rect.top;
                
                if (isDragging) {
                    const value = Math.round(hartslagChart.scales.y.getValueForPixel(y));
                    
                    if (value >= 60 && value <= 200) {
                        if (dragTarget === 'aerobic') {
                            aerobeDrempelInput.value = value;
                        } else if (dragTarget === 'anaerobic') {
                            anaerobeDrempelInput.value = value;
                        }
                        hartslagChart.update('none');
                    }
                } else {
                    // Hover effect
                    const aerobicValue = parseInt(aerobeDrempelInput.value) || 140;
                    const anaerobicValue = parseInt(anaerobeDrempelInput.value) || 160;
                    
                    const aerobicY = hartslagChart.scales.y.getPixelForValue(aerobicValue);
                    const anaerobicY = hartslagChart.scales.y.getPixelForValue(anaerobicValue);
                    
                    if (Math.abs(y - aerobicY) < 15 || Math.abs(y - anaerobicY) < 15) {
                        hartslagChart.canvas.style.cursor = 'ns-resize';
                    } else {
                        hartslagChart.canvas.style.cursor = 'default';
                    }
                }
            });
            
            hartslagChart.canvas.addEventListener('mouseup', function(e) {
                isDragging = false;
                dragTarget = null;
                hartslagChart.canvas.style.cursor = 'default';
            });
            
            hartslagChart.canvas.addEventListener('mouseleave', function(e) {
                isDragging = false;
                dragTarget = null;
                hartslagChart.canvas.style.cursor = 'default';
            });
            
            // Input field change listeners
            aerobeDrempelInput.addEventListener('change', function() {
                hartslagChart.update('none');
            });
            
            anaerobeDrempelInput.addEventListener('change', function() {
                hartslagChart.update('none');
            });
        }
    }
});
</script>
@endsection

@section('form')
<div class="mb-4">
    <label for="zadel_trapas_hoek" class="block text-sm font-medium text-gray-700">Zadel-trapas hoek (graden)</label>
                                <input type="number" 
                                   step="0.1"
                                   name="zadel_trapas_hoek" 
                                   id="zadel_trapas_hoek"
                                   value="{{ old('zadel_trapas_hoek') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
</div>

<div class="mb-4">
    <label for="zadel_trapas_afstand" class="block text-sm font-medium text-gray-700">Zadel-trapas afstand (cm)</label>
                                <input type="number" 
                                   step="0.1"
                                   name="zadel_trapas_afstand" 
                                   id="zadel_trapas_afstand"
                                   value="{{ old('zadel_trapas_afstand') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
</div>

<div class="mb-4">
    <label for="stuur_trapas_hoek" class="block text-sm font-medium text-gray-700">Stuur-trapas hoek (graden)</label>
                                <input type="number" 
                                   step="0.1"
                                   name="stuur_trapas_hoek" 
                                   id="stuur_trapas_hoek"
                                   value="{{ old('stuur_trapas_hoek') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
</div>

<div class="mb-4">
    <label for="stuur_trapas_afstand" class="block text-sm font-medium text-gray-700">Stuur-trapas afstand (cm)</label>
                                <input type="number" 
                                   step="0.1"
                                   name="stuur_trapas_afstand" 
                                   id="stuur_trapas_afstand"
                                   value="{{ old('stuur_trapas_afstand') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
</div>

<div class="mb-4">
    <label for="zadel_lengte" class="block text-sm font-medium text-gray-700">Zadel lengte (center-top in cm)</label>
                                <input type="number" 
                                   step="0.1"
                                   name="zadel_lengte" 
                                   id="zadel_lengte"
                                   value="{{ old('zadel_lengte') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
</div>

<div class="mb-4">
    <label for="beenlengteverschil" class="block text-sm font-medium text-gray-700">Beenlengteverschil (cm)</label>
    <select name="beenlengteverschil" id="beenlengteverschil" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        <option value="0" {{ old('beenlengteverschil') == '0' ? 'selected' : '' }}>Geen</option>
        <option value="1" {{ old('beenlengteverschil') == '1' ? 'selected' : '' }}>Ja, verschil in cm</option>
    </select>
</div>

<div class="mb-4" style="display: none;">
    <label for="beenlengteverschil_cm" class="block text-sm font-medium text-gray-700">Specificeer beenlengteverschil (cm)</label>
    <input type="number" 
           step="0.1"
           name="beenlengteverschil_cm" 
           id="beenlengteverschil_cm"
           value="{{ old('beenlengteverschil_cm') }}"
           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
</div>

<div class="mb-4">
    <label for="lengte" class="block text-sm font-medium text-gray-700">Lengte (cm)</label>
    <input type="number" 
           step="0.1"
           name="lengte" 
           id="lengte"
           value="{{ old('lengte') }}"
           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
</div>

<div class="mb-4">
    <label for="binnenbeenlengte" class="block text-sm font-medium text-gray-700">Binnenbeenlengte (cm)</label>
    <input type="number" 
           step="0.1"
           name="binnenbeenlengte" 
           id="binnenbeenlengte"
           value="{{ old('binnenbeenlengte') }}"
           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
</div>

<div class="mb-4">
    <label for="armlengte" class="block text-sm font-medium text-gray-700">Armlengte (cm)</label>
    <input type="number" 
           step="0.1"
           name="armlengte" 
           id="armlengte"
           value="{{ old('armlengte') }}"
           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
</div>

<div class="mb-4">
    <label for="romplengte" class="block text-sm font-medium text-gray-700">Romplengte (cm)</label>
    <input type="number" 
           step="0.1"
           name="romplengte" 
           id="romplengte"
           value="{{ old('romplengte') }}"
           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
</div>

<div class="mb-4">
    <label for="schouderbreedte" class="block text-sm font-medium text-gray-700">Schouderbreedte (cm)</label>
    <input type="number" 
           step="0.1"
           name="schouderbreedte" 
           id="schouderbreedte"
           value="{{ old('schouderbreedte') }}"
           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
</div>

<div class="mb-4">
    <label for="aanpassing_zadel" class="block text-sm font-medium text-gray-700">Aanpassing zadel (cm)</label>
    <input type="number" 
           step="0.1"
           name="aanpassing_zadel" 
           id="aanpassing_zadel"
           value="{{ old('aanpassing_zadel') }}"
           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
</div>

<div class="mb-4">
    <label for="aanpassing_setback" class="block text-sm font-medium text-gray-700">Aanpassing setback (cm)</label>
    <input type="number" 
           step="0.1"
           name="aanpassing_setback" 
           id="aanpassing_setback"
           value="{{ old('aanpassing_setback') }}"
           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
</div>

<div class="mb-4">
    <label for="aanpassing_reach" class="block text-sm font-medium text-gray-700">Aanpassing reach (cm)</label>
    <input type="number" 
           step="0.1"
           name="aanpassing_reach" 
           id="aanpassing_reach"
           value="{{ old('aanpassing_reach') }}"
           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
</div>

<div class="mb-4">
    <label for="aanpassing_drop" class="block text-sm font-medium text-gray-700">Aanpassing drop (cm)</label>
    <input type="number" 
           step="0.1"
           name="aanpassing_drop" 
           id="aanpassing_drop"
           value="{{ old('aanpassing_drop') }}"
           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
</div>

<div class="mb-4">
    <label for="aanpassing_stuurpen" class="block text-sm font-medium text-gray-700">Aanpassing stuurpen (cm)</label>
    <input type="number" 
           step="0.1"
           name="aanpassing_stuurpen" 
           id="aanpassing_stuurpen"
           value="{{ old('aanpassing_stuurpen') }}"
           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
</div>

<div class="mb-4">
    <label for="schoenmaat" class="block text-sm font-medium text-gray-700">Schoenmaat</label>
    <input type="number"
           step="0.1"
           name="schoenmaat"
           id="schoenmaat"
           value="{{ old('schoenmaat') }}"
           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
</div>
<div class="mb-4">
    <label for="voetbreedte" class="block text-sm font-medium text-gray-700">Voetbreedte (cm)</label>
    <input type="number"
           step="0.1"
           name="voetbreedte"
           id="voetbreedte"
           value="{{ old('voetbreedte') }}"
           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
</div>
@endsection
