@php
    // Get testzadels in gebruik
    $testzadels = collect();
    try {
        if (\Illuminate\Support\Facades\Schema::hasColumn('bikefits', 'nieuw_testzadel')) {
            $testzadels = \App\Models\Bikefit::with(['klant'])
                ->whereNotNull('nieuw_testzadel')
                ->where('nieuw_testzadel', '!=', '')
                ->orderBy('created_at', 'desc')
                ->take(3)
                ->get();
        }
    } catch (\Exception $e) {
        $testzadels = collect();
    }
@endphp

<div style="background:#fff;border-radius:18px;box-shadow:0 2px 8px #e5e7eb;padding:1.2em;min-height:320px;flex:1 1 24%;max-width:48%;box-sizing:border-box;">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-xl font-bold text-gray-900">Testzadels in gebruik</h3>
        <span class="bg-blue-100 text-blue-800 text-xs font-medium w-6 h-6 flex items-center justify-center rounded-full">{{ $testzadels->count() }}</span>
    </div>
    
    @if($testzadels->count() > 0)
        <div class="space-y-3 max-h-64 overflow-y-auto">
            @foreach($testzadels as $bikefit)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border" data-bikefit-id="{{ $bikefit->id }}">
                    <div class="flex-1">
                        <div class="flex items-center space-x-3">
                            <input type="checkbox" 
                                   class="testzadel-checkbox rounded" 
                                   data-bikefit-id="{{ $bikefit->id }}">
                            <div>
                                <p class="font-medium text-gray-900">{{ $bikefit->klant->voornaam }} {{ $bikefit->klant->naam }}</p>
                                <p class="text-sm text-gray-600">{{ $bikefit->nieuw_testzadel }}</p>
                                <p class="text-xs text-gray-500">{{ $bikefit->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                        <div class="mt-2">
                            <input type="text" 
                                   placeholder="Notitie (bijv. betaald, geretourneerd...)" 
                                   class="testzadel-notitie w-full text-xs border-gray-300 rounded px-2 py-1" 
                                   data-bikefit-id="{{ $bikefit->id }}">
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        
        <div class="mt-4 text-center">
            <a href="{{ route('testzadels.index') }}" class="inline-flex items-center px-3 py-2 bg-blue-100 text-blue-800 text-sm font-medium rounded-lg hover:bg-blue-200 transition-colors">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
                Alle testzadels bekijken
            </a>
        </div>
    @else
        <div class="text-center text-gray-500 py-8">
            <p>Geen testzadels in gebruik</p>
        </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle checkbox changes
    document.querySelectorAll('.testzadel-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const bikefitId = this.dataset.bikefitId;
            const status = this.checked ? 'completed' : 'pending';
            updateTestzadelStatus(bikefitId, status, null);
        });
    });

    // Handle note changes with debounce
    let debounceTimers = {};
    document.querySelectorAll('.testzadel-notitie').forEach(input => {
        input.addEventListener('input', function() {
            const bikefitId = this.dataset.bikefitId;
            const notitie = this.value;
            
            // Clear existing timer
            if (debounceTimers[bikefitId]) {
                clearTimeout(debounceTimers[bikefitId]);
            }
            
            // Set new timer
            debounceTimers[bikefitId] = setTimeout(() => {
                updateTestzadelStatus(bikefitId, null, notitie);
            }, 1000);
        });
    });

    function updateTestzadelStatus(bikefitId, status, notitie) {
        const formData = new FormData();
        if (status !== null) formData.append('status', status);
        if (notitie !== null) formData.append('notitie', notitie);
        
        fetch(`/testzadel/${bikefitId}/status`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                console.error('Failed to update testzadel status');
            }
        })
        .catch(error => console.error('Error:', error));
    }
});
</script>