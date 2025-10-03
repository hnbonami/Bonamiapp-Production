#!/bin/bash
# Restore the admin tools functionality

echo "🔧 RESTORING ADMIN TOOLS FUNCTIONALITY"
echo "======================================"

cd /Users/hannesbonami/Herd/app/Bonamiapp

echo "📋 Step 1: Backup StaffNoteController..."
cp app/Http/Controllers/StaffNoteController.php app/Http/Controllers/StaffNoteController.php.backup-$(date +%Y%m%d-%H%M%S)

echo ""
echo "📋 Step 2: Add adminOverview method to StaffNoteController..."

# Add adminOverview method before the closing brace
cat >> temp_admin_method.txt << 'EOF'

    /**
     * Admin overview with database tools
     * Shows tools for uploading/downloading bikefits and klanten
     */
    public function adminOverview()
    {
        // Get statistics for the admin overview
        $stats = [
            'total_notes' => \App\Models\StaffNote::count(),
            'recent_notes' => \App\Models\StaffNote::where('created_at', '>=', now()->subDays(7))->count(),
            'total_bikefits' => \DB::table('bikefits')->count(),
            'total_klanten' => \App\Models\Klant::count(),
        ];
        
        // Get recent notes for admin overview
        $recentNotes = \App\Models\StaffNote::latest()->take(5)->get();
        
        return view('admin.staff-notes-overview', compact('stats', 'recentNotes'));
    }
    
    /**
     * Export bikefits data
     */
    public function exportBikefits()
    {
        $bikefits = \DB::table('bikefits')->get();
        
        $headers = [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="bikefits_export_' . date('Y-m-d') . '.json"',
        ];
        
        return response()->json($bikefits, 200, $headers);
    }
    
    /**
     * Export klanten data
     */
    public function exportKlanten()
    {
        $klanten = \App\Models\Klant::all();
        
        $headers = [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="klanten_export_' . date('Y-m-d') . '.json"',
        ];
        
        return response()->json($klanten, 200, $headers);
    }
EOF

# Insert the new methods before the last closing brace
sed -i.backup '/^}[[:space:]]*$/i\
' app/Http/Controllers/StaffNoteController.php

# Add the methods
sed -i.backup2 '/^}[[:space:]]*$/{
    r temp_admin_method.txt
}' app/Http/Controllers/StaffNoteController.php

# Clean up temp file
rm temp_admin_method.txt

echo "✅ Added adminOverview, exportBikefits, and exportKlanten methods"

echo ""
echo "📋 Step 3: Revert route to use adminOverview again..."

# Change back to adminOverview since we've added the method
sed -i.backup-route 's/->index()/->adminOverview()/' routes/web.php

echo "✅ Route now calls adminOverview() method"

echo ""
echo "📋 Step 4: Create/update admin staff-notes overview view..."

mkdir -p resources/views/admin

cat > resources/views/admin/staff-notes-overview.blade.php << 'EOF'
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Admin Tools & Database Management</h1>
        <p class="text-gray-600 mt-2">Beheer database exports, imports en systeem overzicht</p>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-sm font-medium text-gray-500">Totaal Staff Notes</h3>
            <p class="text-3xl font-bold text-blue-600">{{ $stats['total_notes'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-sm font-medium text-gray-500">Deze Week</h3>
            <p class="text-3xl font-bold text-green-600">{{ $stats['recent_notes'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-sm font-medium text-gray-500">Totaal Bikefits</h3>
            <p class="text-3xl font-bold text-purple-600">{{ $stats['total_bikefits'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-sm font-medium text-gray-500">Totaal Klanten</h3>
            <p class="text-3xl font-bold text-orange-600">{{ $stats['total_klanten'] ?? 0 }}</p>
        </div>
    </div>

    <!-- Database Tools -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Export Tools -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">📥 Database Export</h2>
            <p class="text-gray-600 mb-4">Download database gegevens voor backup of analyse</p>
            
            <div class="space-y-3">
                <a href="{{ route('admin.export.bikefits') }}" 
                   class="block w-full bg-blue-600 text-white text-center py-2 px-4 rounded-md hover:bg-blue-700 transition-colors">
                    Export Bikefits Data
                </a>
                
                <a href="{{ route('admin.export.klanten') }}" 
                   class="block w-full bg-green-600 text-white text-center py-2 px-4 rounded-md hover:bg-green-700 transition-colors">
                    Export Klanten Data
                </a>
                
                <a href="{{ route('staffnotes.index') }}" 
                   class="block w-full bg-gray-600 text-white text-center py-2 px-4 rounded-md hover:bg-gray-700 transition-colors">
                    Bekijk Staff Notes
                </a>
            </div>
        </div>

        <!-- Upload Tools -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">📤 Database Import</h2>
            <p class="text-gray-600 mb-4">Upload en importeer database gegevens</p>
            
            <form action="#" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Selecteer bestand (JSON format)
                    </label>
                    <input type="file" name="import_file" accept=".json"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <button type="submit" 
                        class="w-full bg-orange-600 text-white py-2 px-4 rounded-md hover:bg-orange-700 transition-colors">
                    Import Data (Coming Soon)
                </button>
            </form>
        </div>
    </div>

    <!-- Recent Staff Notes -->
    @if(isset($recentNotes) && $recentNotes->count() > 0)
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-900">📝 Recente Staff Notes</h2>
        </div>
        <div class="divide-y divide-gray-200">
            @foreach($recentNotes as $note)
            <div class="p-6">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="font-semibold text-gray-900">{{ $note->titel ?? 'Geen titel' }}</h3>
                        <p class="text-gray-600 text-sm mt-1">{{ Str::limit($note->inhoud ?? '', 100) }}</p>
                    </div>
                    <span class="text-xs text-gray-500">{{ $note->created_at->diffForHumans() }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
EOF

echo "✅ Created admin tools overview view"

echo ""
echo "📋 Step 5: Add export routes..."

# Add export routes to web.php
cat >> routes_addition.txt << 'EOF'

// Admin export routes
Route::middleware(['auth', 'verified'])->group(function() {
    Route::get('/admin/export/bikefits', [App\Http\Controllers\StaffNoteController::class, 'exportBikefits'])->name('admin.export.bikefits');
    Route::get('/admin/export/klanten', [App\Http\Controllers\StaffNoteController::class, 'exportKlanten'])->name('admin.export.klanten');
});
EOF

# Add routes before the last require statement
sed -i.backup-routes '/require __DIR__/i\
' routes/web.php

sed -i.backup-routes2 '/require __DIR__/{
    r routes_addition.txt
}' routes/web.php

rm routes_addition.txt

echo "✅ Added export routes"

echo ""
echo "📋 Step 6: Clear caches..."
php artisan route:clear
php artisan config:clear
php artisan view:clear

echo ""
echo "🎉 ADMIN TOOLS RESTORED!"
echo "======================="
echo "✅ Added adminOverview() method to StaffNoteController"
echo "✅ Created admin tools overview view with export functionality"
echo "✅ Added export routes for bikefits and klanten"
echo "✅ Route now correctly calls adminOverview()"
echo ""
echo "🧪 TEST NOW:"
echo "Click on 'Beheer' button - should show admin tools with:"
echo "- Statistics dashboard"
echo "- Database export buttons (Bikefits & Klanten)"
echo "- Database import form"
echo "- Recent staff notes"