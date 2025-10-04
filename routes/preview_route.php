// Sjablonen preview functionality - must be above resource routes
Route::get('sjablonen/{id}/preview', [\App\Http\Controllers\SjablonenController::class, 'preview'])->name('sjablonen.preview');