<!-- Sjablonen Menu Item -->
<!-- Voeg dit toe aan je navigatie menu waar de andere menu items staan -->
<a href="{{ route('sjablonen.index') }}" 
   class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-100 {{ request()->routeIs('sjablonen.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700' }}">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
    </svg>
    <span>Sjablonen</span>
</a>