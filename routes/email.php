<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\EmailController;

/*
|--------------------------------------------------------------------------
| Email Management Routes
|--------------------------------------------------------------------------
|
| Alle routes voor email template beheer, triggers, settings, etc.
| Gescheiden van web.php voor betere organisatie
|
*/

Route::middleware(['auth'])->prefix('admin/email')->name('admin.email.')->group(function () {
    
    // Dashboard / Overview
    Route::get('/', [EmailController::class, 'index'])->name('index');
    
    // Email Templates
    Route::get('templates', [EmailController::class, 'templates'])->name('templates');
    Route::get('templates/create', [EmailController::class, 'createTemplate'])->name('templates.create');
    Route::post('templates', [EmailController::class, 'storeTemplate'])->name('templates.store');
    Route::get('templates/{id}/edit', [EmailController::class, 'editTemplate'])->name('templates.edit');
    Route::put('templates/{id}', [EmailController::class, 'updateTemplate'])->name('templates.update');
    Route::delete('templates/{id}', [EmailController::class, 'destroyTemplate'])->name('templates.destroy');
    Route::get('templates/{id}/preview', [EmailController::class, 'previewTemplate'])->name('templates.preview');
    Route::delete('templates/{id}/delete', [EmailController::class, 'deleteTemplate'])->name('templates.delete');
    
    // Template Initialisatie & Cloning
    Route::post('templates/initialize', [EmailController::class, 'initializeTemplates'])->name('templates.initialize');
    Route::post('templates/{id}/clone', [EmailController::class, 'cloneTemplate'])->name('templates.clone');
    
    // Email Triggers
    Route::get('triggers', [EmailController::class, 'triggers'])->name('triggers');
    Route::get('triggers/create', [EmailController::class, 'createTrigger'])->name('triggers.create');
    Route::get('triggers/{id}/edit', [EmailController::class, 'editTrigger'])->name('triggers.edit');
    Route::put('triggers/{id}', [EmailController::class, 'updateTrigger'])->name('triggers.update');
    Route::post('triggers/test', [EmailController::class, 'testTriggers'])->name('triggers.test');
    Route::post('triggers/setup', [EmailController::class, 'setupTriggers'])->name('triggers.setup');
    Route::post('triggers/{triggerType}/run', [EmailController::class, 'runTrigger'])->name('triggers.run');
    
    // Email Logs
    Route::get('logs', [EmailController::class, 'logs'])->name('logs');
    
    // Email Settings
    Route::get('settings', [EmailController::class, 'settings'])->name('settings');
    Route::post('settings', [EmailController::class, 'updateSettings'])->name('settings.update');
    
    // Bulk Email
    Route::get('bulk', [EmailController::class, 'bulkEmail'])->name('bulk');
    Route::post('bulk/customers', [EmailController::class, 'sendBulkToCustomers'])->name('bulk.customers');
    Route::post('bulk/employees', [EmailController::class, 'sendBulkToEmployees'])->name('bulk.employees');
    Route::post('preview', [EmailController::class, 'previewEmail'])->name('preview');
    
    // Unsubscribe Management
    Route::get('unsubscribed', [EmailController::class, 'unsubscribed'])->name('unsubscribed');
    Route::post('resubscribe/{id}', [EmailController::class, 'resubscribe'])->name('resubscribe');
    
    // Test & Debug
    Route::get('send-test', [EmailController::class, 'sendTestEmail'])->name('send-test');
    Route::post('migrate-templates', [EmailController::class, 'migrateTemplates'])->name('migrate-templates');
});

// Public Unsubscribe Routes (geen auth vereist)
Route::get('email/unsubscribe/{token}', [EmailController::class, 'showUnsubscribe'])->name('email.unsubscribe');
Route::post('email/unsubscribe/{token}', [EmailController::class, 'processUnsubscribe'])->name('email.unsubscribe.process');