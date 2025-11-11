@extends('layouts.app')

@section('title', 'Sjablonen Manager')

@section('content')
    <!-- Sjablonen Button Enhancements -->
    <link rel="stylesheet" href="/css/sjablonen-editor-buttons.css">

    <!-- Header -->
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-6 py-4">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Sjablonen Manager</h1>
                    <p class="text-sm text-gray-600">Beheer je document sjablonen</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('sjablonen.create') }}" 
                       class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium text-gray-700 hover:text-gray-900 transition-all duration-200"
                       style="background-color: #c8e1eb; border: 1px solid #a5c9d6;">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Nieuw Sjabloon
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="mb-6">
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                {{ session('success') }}
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6">
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                {{ session('error') }}
            </div>
        </div>
    @endif


    <!-- Main Content -->
    @if($sjablonen->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($sjablonen as $sjabloon)
                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-200">
                    <!-- Card Header -->
                    <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-indigo-50 border-b">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">{{ $sjabloon->naam }}</h3>
                                <div class="flex items-center space-x-2 mt-1">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ ucfirst($sjabloon->categorie) }}
                                    </span>
                                    @if($sjabloon->testtype)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            {{ $sjabloon->testtype }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            
                            @if(auth()->user()->role === 'superadmin')
                                <!-- Superadmin: Toon shared badge -->
                                @if($sjabloon->organisatie_id == 1)
                                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold shadow-md" style="background: linear-gradient(135deg, #8b5cf6 0%, #ec4899 100%); color: white;">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                        Standaard Sjabloon
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-gray-200 text-gray-700">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                                        </svg>
                                        Organisatie: {{ $sjabloon->organisatie->naam ?? 'Onbekend' }}
                                    </span>
                                @endif
                            @else
                                <!-- Niet-superadmin: Toon alleen read-only badge als het een shared template is -->
                                @if(!$heeftRapportenOpmaken && (is_null($sjabloon->organisatie_id) || $sjabloon->organisatie_id == 1))
                                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                        </svg>
                                        Alleen Lezen
                                    </span>
                                @endif
                            @endif
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div class="px-6 py-4">
                        @if($sjabloon->beschrijving)
                            <p class="text-gray-600 text-sm mb-4">{{ Str::limit($sjabloon->beschrijving, 100) }}</p>
                        @endif
                        
                        <div class="text-xs text-gray-500 mb-4">
                            <p>Aangemaakt: {{ $sjabloon->created_at->format('d-m-Y H:i') }}</p>
                            @if($sjabloon->updated_at != $sjabloon->created_at)
                                <p>Laatst bewerkt: {{ $sjabloon->updated_at->format('d-m-Y H:i') }}</p>
                            @endif
                        </div>
                    </div>

                    <!-- Card Actions -->
                    <div class="px-6 py-4 bg-gray-50 border-t">
                        <div class="flex items-center justify-between">
                            <!-- Eerste drie knoppen in kleur #c8e1eb -->
                            <div class="flex space-x-3">
                                <a href="{{ route('sjablonen.edit-basic', $sjabloon) }}" 
                                   class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium text-gray-700 hover:text-gray-900 transition-all duration-200"
                                   style="background-color: #c8e1eb; border: 1px solid #a5c9d6;">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                    Bewerken
                                </a>
                                
                                <a href="{{ route('sjablonen.preview', $sjabloon) }}" 
                                   class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium text-gray-700 hover:text-gray-900 transition-all duration-200"
                                   style="background-color: #c8e1eb; border: 1px solid #a5c9d6;">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    Bekijken
                                </a>
                                
                                <button type="button" onclick="duplicateTemplate({{ $sjabloon->id }})" 
                                        class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium text-gray-700 hover:text-gray-900 transition-all duration-200"
                                        style="background-color: #c8e1eb; border: 1px solid #a5c9d6;">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                    </svg>
                                    Dupliceren
                                </button>
                            </div>
                            
                            <!-- Verwijder knop met prullenbak icoon -->
                            <button type="button" onclick="deleteTemplate({{ $sjabloon->id }}, '{{ addslashes($sjabloon->naam) }}')" 
                                    class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-white border border-gray-300 text-gray-600 hover:text-black hover:border-gray-400 transition-all duration-200 shadow-sm">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <!-- Empty State -->
        <div class="text-center py-12">
            <div class="mx-auto h-24 w-24 text-gray-400">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
            <h3 class="mt-4 text-lg font-medium text-gray-900">Geen sjablonen gevonden</h3>
            <p class="mt-2 text-gray-500">Maak je eerste sjabloon aan om te beginnen.</p>
            <div class="mt-6">
                <a href="{{ route('sjablonen.create') }}" 
                   class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    + Eerste Sjabloon Aanmaken
                </a>
            </div>
        </div>
    @endif
@endsection

<script>
function duplicateTemplate(sjabloonId) {
    console.log('Duplicate clicked for sjabloon:', sjabloonId);
    
    if (confirm('Weet je zeker dat je dit sjabloon wilt dupliceren?')) {
        // Create a form to submit the duplicate request
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/sjablonen/${sjabloonId}/duplicate`;
        
        // Add CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        if (!csrfToken) {
            alert('CSRF token niet gevonden!');
            return;
        }
        
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = csrfToken;
        form.appendChild(csrfInput);
        
        console.log('Submitting duplicate form to:', form.action);
        
        // Submit the form
        document.body.appendChild(form);
        form.submit();
    }
}

function deleteTemplate(sjabloonId, sjabloonNaam) {
    console.log('Delete clicked for sjabloon:', sjabloonId, 'naam:', sjabloonNaam);
    
    if (confirm(`Weet je zeker dat je het sjabloon "${sjabloonNaam}" definitief wilt verwijderen? Deze actie kan niet ongedaan worden gemaakt.`)) {
        // Create a form to submit the delete request
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/sjablonen/${sjabloonId}`;
        
        // Add CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        if (!csrfToken) {
            alert('CSRF token niet gevonden!');
            return;
        }
        
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = csrfToken;
        form.appendChild(csrfInput);
        
        // Add DELETE method
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        form.appendChild(methodInput);
        
        console.log('Submitting delete form to:', form.action);
        
        // Submit the form
        document.body.appendChild(form);
        form.submit();
    }
}

// Debug: Log when page loads
document.addEventListener('DOMContentLoaded', function() {
    console.log('Sjablonen page loaded');
    console.log('Duplicate buttons found:', document.querySelectorAll('button[onclick*="duplicateTemplate"]').length);
    console.log('Delete buttons found:', document.querySelectorAll('button[onclick*="deleteTemplate"]').length);
});
</script>