<?php

// Quick fix to add preview and PDF routes temporarily
Route::get('sjablonen/{id}/preview', [\App\Http\Controllers\SjablonenController::class, 'preview'])->name('sjablonen.preview');
Route::get('sjablonen/{template}/generate-pdf/{klant?}/{test_id?}/{type?}', [\App\Http\Controllers\SjablonenController::class, 'generatePdf'])->name('sjablonen.generate-pdf');

// Alternative simple PDF route for direct access
Route::get('sjablonen/{id}/pdf', [\App\Http\Controllers\SjablonenController::class, 'generatePdf'])->name('sjablonen.pdf');