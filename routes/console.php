<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('diagnose:dashboard', function () {
    $this->info('🔍 Dashboard Content Diagnostics');
    $this->info('=================================');
    $this->newLine();
    
    // 1. Database tabel check
    $this->info('📊 1. DATABASE TABEL CHECK');
    $tableExists = Schema::hasTable('dashboard_content');
    $this->line("   Tabel bestaat: " . ($tableExists ? '✅' : '❌'));
    
    if ($tableExists) {
        $columns = Schema::getColumnListing('dashboard_content');
        $this->line("   Kolommen: " . implode(', ', $columns));
        
        $hasOrgId = in_array('organisatie_id', $columns);
        $this->line("   organisatie_id kolom: " . ($hasOrgId ? '✅ JA' : '❌ NEE - RUN MIGRATIE!'));
    }
    $this->newLine();
    
    // 2. Users check
    $this->info('👥 2. USERS & ORGANISATIES');
    $users = \App\Models\User::all();
    foreach ($users as $user) {
        $this->line(sprintf(
            "   %s (ID:%d, Role:%s, Org:%s)",
            $user->name,
            $user->id,
            $user->role,
            $user->organisatie_id ?? '❌ GEEN'
        ));
    }
    $this->newLine();
    
    // 3. Bestaande content
    $this->info('📝 3. BESTAANDE DASHBOARD CONTENT');
    $contentCount = \App\Models\DashboardContent::count();
    $this->line("   Totaal items: {$contentCount}");
    
    if ($contentCount > 0) {
        $items = \App\Models\DashboardContent::all();
        foreach ($items as $item) {
            $this->line(sprintf(
                "   [%d] %s (Org:%s, Vis:%s, Archived:%s)",
                $item->id,
                $item->titel,
                $item->organisatie_id ?? '❌',
                $item->visibility ?? '?',
                $item->is_archived ? 'JA' : 'NEE'
            ));
        }
    } else {
        $this->warn('   ⚠️  Geen content gevonden in database!');
    }
    $this->newLine();
    
    // 4. Routes check
    $this->info('🛣️  4. ROUTES CHECK');
    $routes = collect(Route::getRoutes())->filter(function($route) {
        return str_contains($route->getName() ?? '', 'dashboard-content');
    });
    
    if ($routes->isEmpty()) {
        $this->error('   ❌ GEEN dashboard-content routes gevonden!');
    } else {
        $this->line("   ✅ {$routes->count()} routes gevonden");
        foreach ($routes as $route) {
            $this->line("   - " . $route->getName());
        }
    }
    $this->newLine();
    
    // 5. Permissions check
    $this->info('🔐 5. PERMISSIONS');
    $adminUser = \App\Models\User::where('role', 'admin')->orWhere('role', 'medewerker')->first();
    if ($adminUser) {
        $this->line("   Admin/Medewerker gevonden: {$adminUser->name}");
        $canCreate = in_array($adminUser->role, ['admin', 'medewerker']);
        $this->line("   Kan content maken: " . ($canCreate ? '✅' : '❌'));
    } else {
        $this->error('   ❌ Geen admin/medewerker gevonden!');
    }
    $this->newLine();
    
    // 6. Suggesties
    $this->info('💡 SUGGESTIES:');
    if (!$tableExists) {
        $this->warn('   ⚠️  Run: php artisan migrate');
    }
    if ($tableExists && !in_array('organisatie_id', Schema::getColumnListing('dashboard_content'))) {
        $this->warn('   ⚠️  Run: php artisan migrate (voor organisatie_id kolom)');
    }
    if ($contentCount === 0) {
        $this->warn('   ⚠️  Probeer handmatig content aan te maken via /dashboard-content/create');
    }
    
    $this->newLine();
    $this->info('=================================');
    $this->info('✅ Diagnostics voltooid!');
    
})->purpose('Diagnosticeer dashboard content problemen');
