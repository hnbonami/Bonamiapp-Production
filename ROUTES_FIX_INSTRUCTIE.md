// PARSE ERROR FIX INSTRUCTIES:
// Ga naar routes/web.php rond regel 1324
// Zoek naar de email template routes sectie
// Zorg dat deze correct afgesloten wordt met } of ]);

// De routes zouden er zo uit moeten zien:

Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    
    // ...existing routes...
    
    // Email template routes
    Route::prefix('email')->group(function () {
        Route::get('/templates', [\App\Http\Controllers\Admin\EmailTemplateController::class, 'index'])->name('admin.email.templates');
        Route::get('/templates/create', [\App\Http\Controllers\Admin\EmailTemplateController::class, 'create'])->name('admin.email.templates.create');
        Route::post('/templates', [\App\Http\Controllers\Admin\EmailTemplateController::class, 'store'])->name('admin.email.templates.store');
        Route::get('/templates/{template}/edit', [\App\Http\Controllers\Admin\EmailTemplateController::class, 'edit'])->name('admin.email.templates.edit');
        Route::put('/templates/{template}', [\App\Http\Controllers\Admin\EmailTemplateController::class, 'update'])->name('admin.email.templates.update');
        Route::delete('/templates/{template}', [\App\Http\Controllers\Admin\EmailTemplateController::class, 'destroy'])->name('admin.email.templates.destroy');
        Route::get('/templates/{template}/preview', [\App\Http\Controllers\Admin\EmailTemplateController::class, 'preview'])->name('admin.email.templates.preview');
        Route::post('/templates/reset-defaults', [\App\Http\Controllers\Admin\EmailTemplateController::class, 'resetDefaultTemplates'])->name('admin.email.templates.reset');
    });
    
}); // <- ZORG DAT DEZE ACCOLADE ER IS!

// Als je een syntax error hebt, tel de opening [ en { en sluit ze allemaal met ] en }
