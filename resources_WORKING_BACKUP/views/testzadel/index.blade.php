@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-3xl font-extrabold text-gray-900">Testzadels Overzicht</h1>
        <p class="text-gray-600 mt-2">Alle klanten met een testzadel in gebruik</p>
    </div>

    <div class="bg-white shadow overflow-hidden sm:rounded-md">
        @if($testzadels->count() > 0)
            <ul class="divide-y divide-gray-200">
                @foreach($testzadels as $bikefit)
                    <li class="px-6 py-4" data-bikefit-id="{{ $bikefit->id }}">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <input type="checkbox" 
                                       class="testzadel-checkbox rounded h-5 w-5" 
                                       data-bikefit-id="{{ $bikefit->id }}"
                                       {{ optional($bikefit->testzadelStatus)->status === 'completed' ? 'checked' : '' }}>
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900">
                                        {{ $bikefit->klant->voornaam }} {{ $bikefit->klant->naam }}
                                    </h3>
                                    <p class="text-sm text-gray-600">Testzadel: <strong>{{ $bikefit->nieuw_testzadel }}</strong></p>
                                    <p class="text-xs text-gray-500">
                                        Bikefit datum: {{ $bikefit->created_at->format('d-m-Y') }} ({{ $bikefit->created_at->diffForHumans() }})
                                    </p>
                                </div>
                            </div>
                            <div class="flex-shrink-0">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    {{ optional($bikefit->testzadelStatus)->status === 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ optional($bikefit->testzadelStatus)->status === 'completed' ? 'Afgerond' : 'In gebruik' }}
                                </span>
                            </div>
                        </div>
                        <div class="mt-3">
                            <input type="text" 
                                   placeholder="Notitie (bijv. betaald, geretourneerd, vervangen...)" 
                                   class="testzadel-notitie w-full border-gray-300 rounded-md px-3 py-2 text-sm" 
                                   data-bikefit-id="{{ $bikefit->id }}"
                                   value="{{ optional($bikefit->testzadelStatus)->notitie }}">
                        </div>
                    </li>
                @endforeach
            </ul>
        @else
            <div class="text-center py-12">
                <p class="text-gray-500">Geen testzadels in gebruik gevonden.</p>
            </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle checkbox changes
    document.querySelectorAll('.testzadel-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const bikefitId = this.dataset.bikefitId;
            const status = this.checked ? 'completed' : 'pending';
            updateTestzadelStatus(bikefitId, status, null);
            
            // Update visual feedback
            const listItem = this.closest('li');
            const statusBadge = listItem.querySelector('.inline-flex');
            if (this.checked) {
                statusBadge.className = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800';
                statusBadge.textContent = 'Afgerond';
            } else {
                statusBadge.className = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800';
                statusBadge.textContent = 'In gebruik';
            }
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
@endsection