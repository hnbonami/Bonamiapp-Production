<!-- Sidebar Navigation -->
<nav class="bg-white border-r border-gray-200 w-64 min-h-screen">
    <div class="p-4">
        <!-- Dashboard -->
        <a href="{{ route('dashboard') }}" 
           class="flex items-center px-4 py-2 text-sm font-medium rounded-md {{ request()->routeIs('dashboard') ? 'bg-indigo-100 text-indigo-700' : 'text-gray-600 hover:bg-gray-50' }}">
            <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
            </svg>
            Dashboard
        </a>

        <!-- Klanten -->
        <a href="{{ route('klanten.index') }}" 
           class="flex items-center px-4 py-2 text-sm font-medium rounded-md {{ request()->routeIs('klanten.*') ? 'bg-indigo-100 text-indigo-700' : 'text-gray-600 hover:bg-gray-50' }}">
            <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
            </svg>
            Klanten
        </a>

        <!-- Inspanningstests -->
        <a href="{{ route('inspanningstests.index') }}" 
           class="flex items-center px-4 py-2 text-sm font-medium rounded-md {{ request()->routeIs('inspanningstests.*') ? 'bg-indigo-100 text-indigo-700' : 'text-gray-600 hover:bg-gray-50' }}">
            <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
            </svg>
            Inspanningstests
        </a>

        <!-- Nieuwsbrief -->
        <a href="{{ route('nieuwsbrief') }}" 
           class="flex items-center px-4 py-2 text-sm font-medium rounded-md {{ request()->routeIs('nieuwsbrief') ? 'bg-indigo-100 text-indigo-700' : 'text-gray-600 hover:bg-gray-50' }}">
            <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9.5a2.5 2.5 0 00-2.5-2.5H15"></path>
            </svg>
            Nieuwsbrief
        </a>
    </div>
</nav>