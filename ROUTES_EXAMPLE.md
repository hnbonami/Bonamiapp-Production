// Dashboard routes - nieuw widget systeem
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
    Route::get('/dashboard/widgets/create', [DashboardController::class, 'create'])->name('dashboard.widgets.create');
    Route::post('/dashboard/widgets', [DashboardController::class, 'store'])->name('dashboard.widgets.store');
    Route::get('/dashboard/widgets/{widget}/edit', [DashboardController::class, 'edit'])->name('dashboard.widgets.edit');
    Route::put('/dashboard/widgets/{widget}', [DashboardController::class, 'update'])->name('dashboard.widgets.update');
    Route::post('/dashboard/widgets/layout', [DashboardController::class, 'updateLayout'])->name('dashboard.widgets.updateLayout');
    Route::post('/dashboard/widgets/{widget}/toggle', [DashboardController::class, 'toggleVisibility'])->name('dashboard.widgets.toggle');
    Route::delete('/dashboard/widgets/{widget}', [DashboardController::class, 'destroy'])->name('dashboard.widgets.destroy');
    
    // Live stats API endpoints
    Route::get('/dashboard/stats/live', [DashboardStatsController::class, 'getLiveStats'])->name('dashboard.stats.live');
    Route::get('/dashboard/stats/widget', [DashboardStatsController::class, 'getWidgetData'])->name('dashboard.stats.widget');
    Route::get('/dashboard/calendar/events', [DashboardStatsController::class, 'getCalendarEvents'])->name('dashboard.calendar.events');
});
