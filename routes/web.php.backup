<?php

// SJABLOON-MANAGER ROUTE - NAAR TEMPLATES INDEX BLADE!
Route::get('/sjabloon-manager', function() {
    // Get templates data (assuming you have a Template model)
    $templates = \App\Models\Template::all();
    
    // Return the templates index view
    return view('templates.index', compact('templates'));
})->name('sjabloon-manager')->middleware('auth');


// ULTIMATE TEST - BOVENAAN!
Route::get('/ultimate-test', function() {
    return 'ULTIMATE TEST WERKT!!! Cache is cleared en routes worden geladen!';
});

// ABSOLUTE TEST ROUTE - THIS MUST WORK!!
Route::get('/test-route-werkt', function() {
    return 'TEST ROUTE WERKT!!! Als je dit ziet, werken route wijzigingen wel.';
});

// TEMPLATES ROUTE MOVED TO END OF FILE TO AVOID CONFLICTS

// Debug route zonder middleware
Route::get('/debug/auth-status', function() {
    \Log::info('DEBUG AUTH STATUS', [
        'user' => auth()->user(),
        'user_id' => optional(auth()->user())->id,
        'is_authenticated' => auth()->check(),
        'is_verified' => optional(auth()->user())->hasVerifiedEmail(),
        'request_ip' => request()->ip(),
    ]);
    return response()->json([
        'user' => auth()->user(),
        'user_id' => optional(auth()->user())->id,
        'is_authenticated' => auth()->check(),
        'is_verified' => optional(auth()->user())->hasVerifiedEmail(),
        'request_ip' => request()->ip(),
    ]);
});
// Staff notes (notities & taken) routes
use App\Http\Controllers\StaffNoteController;
Route::middleware(['auth', 'verified'])->group(function() {
    Route::resource('staff-notes', StaffNoteController::class)->names([
        'index' => 'staffnotes.index',
        'create' => 'staffnotes.create',
        'store' => 'staffnotes.store',
        'edit' => 'staffnotes.edit',
        'update' => 'staffnotes.update',
        'destroy' => 'staffnotes.destroy'
    ]);
    Route::post('/staff-notes/mark-all-read', [StaffNoteController::class, 'markAllNotesRead'])->name('staffnotes.markAllRead');
});
// Printvriendelijke rapportweergave (zonder knoppen/layout)
Route::get('/klanten/{klant}/bikefit/{bikefit}/print-report', [\App\Http\Controllers\BikefitResultsController::class, 'printReport'])
    ->name('bikefit.report.print');

// Tijdelijke signed route voor PDF-generatie zonder login
// Tijdelijke signed route voor PDF-generatie zonder login
Route::get('/klanten/{klant}/bikefit/{bikefit}/generate-report-signed', [\App\Http\Controllers\BikefitResultsController::class, 'reportPreviewSigned'])
    ->name('bikefit.reportPreview.signed')
    ->middleware('signed');
Route::post('/background-pdf/upload', [\App\Http\Controllers\BackgroundPdfController::class, 'upload'])->name('background_pdf.upload');
Route::post('/backgrounds/convert', [\App\Http\Controllers\BackgroundPdfController::class, 'convert']);

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BikefitController;
use App\Http\Controllers\InspanningstestController;
use App\Http\Controllers\KlantController;
use App\Http\Controllers\MedewerkerController;
use App\Exports\MedewerkersExport;
use App\Exports\KlantenExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\InstagramPostController;
// use App\Http\Controllers\TemplateController; // TEMP DISABLED TO CHECK CONFLICT

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    // For guests we show the login page as the public landing
    return redirect()->route('login');
});

Route::get('/dashboard', [App\Http\Controllers\DashboardContentController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');
// (debug route verwijderd)

// Nieuwsbrief pagina (placeholder)
Route::get('/nieuwsbrief', function () {
    return view('nieuwsbrief');
})->middleware(['auth', 'verified'])->name('nieuwsbrief');

// Nieuwsbrief beheer
Route::middleware(['auth','verified'])->group(function() {
    Route::get('/nieuwsbrieven', [NewsletterController::class, 'index'])->name('newsletters.index');
    // Allow navigating to /nieuwsbrieven/nieuw to create a new draft via GET
    Route::get('/nieuwsbrieven/nieuw', [NewsletterController::class, 'create'])->name('newsletters.new');
    // Existing POST route that creates a draft (keeps legacy behavior/name)
    Route::post('/nieuwsbrieven', [NewsletterController::class, 'create'])->name('newsletters.create');
        Route::delete('/nieuwsbrieven/{newsletter}', [NewsletterController::class, 'destroy'])->name('newsletters.destroy');
    Route::get('/nieuwsbrieven/{newsletter}/bewerken', [NewsletterController::class, 'edit'])->name('newsletters.edit');
    Route::post('/nieuwsbrief/upload', [NewsletterController::class, 'upload'])->name('newsletters.upload');
    Route::post('/nieuwsbrieven/{newsletter}/opslaan', [NewsletterController::class, 'save'])->name('newsletters.save');
    Route::post('/nieuwsbrieven/{newsletter}/ontvangers', [NewsletterController::class, 'recipients'])->name('newsletters.recipients');
    Route::get('/nieuwsbrieven/{newsletter}/preview', [NewsletterController::class, 'preview'])->name('newsletters.preview');
    Route::get('/nieuwsbrieven/{newsletter}/export', [NewsletterController::class, 'export'])->name('newsletters.export');
    Route::post('/nieuwsbrieven/{newsletter}/test', [NewsletterController::class, 'sendTest'])->name('newsletters.test');
    Route::post('/nieuwsbrieven/{newsletter}/verzenden', [NewsletterController::class, 'sendAll'])->name('newsletters.send');

    // Instagram post builder
    Route::get('/instagram', [InstagramPostController::class, 'index'])->name('instagram.index');
    Route::get('/instagram/nieuw', [InstagramPostController::class, 'create'])->name('instagram.create');
    Route::get('/instagram/{post}/bewerken', [InstagramPostController::class, 'edit'])->name('instagram.edit');
    Route::post('/instagram/upload', [InstagramPostController::class, 'upload'])->name('instagram.upload');
    Route::post('/instagram', [InstagramPostController::class, 'store'])->name('instagram.store');
    Route::put('/instagram/{post}', [InstagramPostController::class, 'update'])->name('instagram.update');
    Route::delete('/instagram/{post}', [InstagramPostController::class, 'destroy'])->name('instagram.destroy');
});

// Profile routes: allow authenticated users to edit their profile
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    // Modal partial for AJAX profile edits
    Route::get('/profile/modal', [ProfileController::class, 'modal'])->name('profile.modal');
    // Accept both PATCH and PUT here because some forms/clients may submit PUT
    Route::match(['patch','put'], '/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
// Conflict resolved - keeping both changes

    // Add missing profile modal route
    Route::get('/profile/modal', function () {
        return view('profile.modal');
    })->name('profile.modal');


    // Authenticated routes (profile endpoints are intentionally omitted)

    Route::resource('klanten', KlantController::class)->parameters(['klanten' => 'klant']);
    Route::post('klanten/{klant}/uitnodiging', [KlantController::class, 'sendInvitation'])->name('klanten.sendInvitation');
    Route::post('klanten/{klant}/verwijder', [KlantController::class, 'verwijderViaPost'])->name('klanten.verwijderViaPost');
    // Alleen profielfoto wijzigen (klant)
    Route::post('klanten/{klant}/avatar', [KlantController::class, 'updateAvatar'])->name('klanten.avatar');
    
    Route::resource('medewerkers', MedewerkerController::class)->except(['show', 'destroy']);
    Route::get('medewerkers/{medewerker}', [MedewerkerController::class, 'show'])->name('medewerkers.show');
    Route::delete('medewerkers/{medewerker}', [MedewerkerController::class, 'destroy'])->name('medewerkers.destroy');
    // Alleen profielfoto wijzigen (medewerker)
    Route::post('medewerkers/{medewerker}/avatar', [MedewerkerController::class, 'updateAvatar'])->name('medewerkers.avatar');

    // Bikefit routes
    Route::group(['prefix' => 'klanten/{klant}'], function () {
        // Printvriendelijke rapportweergave (zonder knoppen/layout) - nu binnen de juiste groep
        Route::get('bikefit/{bikefit}/print-report', [\App\Http\Controllers\BikefitResultsController::class, 'printReport'])->name('bikefit.report.print');
        // Perfect print route (zonder navigatie/sidebar)
        Route::get('bikefit/{bikefit}/print-perfect', [\App\Http\Controllers\BikefitResultsController::class, 'printPerfect'])->name('bikefit.report.print.perfect');
    // Pixel-perfecte PDF via Browsershot/Puppeteer
    Route::get('bikefit/{bikefit}/download-pdf-preview', [\App\Http\Controllers\PdfController::class, 'generatePdf'])->name('bikefit.report.pdf.preview');
    // Eenvoudige print-versie (browser native print/PDF)
    Route::get('bikefit/{bikefit}/print', [\App\Http\Controllers\PdfController::class, 'printOnly'])->name('bikefit.report.print');
    // Alternatieve print route via BikefitResultsController
    Route::get('bikefit/{bikefit}/print-direct', [\App\Http\Controllers\BikefitResultsController::class, 'printReport'])->name('bikefit.report.print.direct');
    // Direct PDF download route voor bikefit rapport
    Route::get('bikefit/{bikefit}/download-pdf', [\App\Http\Controllers\PdfController::class, 'exportPdf'])->name('bikefit.report.pdf');
    // Bikefit berekende resultaten en verslag generatie
    Route::get('bikefit/{bikefit}/results', [\App\Http\Controllers\BikefitResultsController::class, 'show'])->name('bikefit.results');
    Route::post('bikefit/{bikefit}/generate-report', [\App\Http\Controllers\BikefitResultsController::class, 'generateReport'])->name('bikefit.generateReport');
    Route::get('bikefit/{bikefit}/generate-report', [\App\Http\Controllers\BikefitResultsController::class, 'generateReport'])->name('bikefit.reportPreview');
        Route::get('bikefit/nieuw', [BikefitController::class, 'create'])->name('bikefit.create');
        Route::post('bikefit', [BikefitController::class, 'store'])->name('bikefit.store');
        Route::get('bikefit/{bikefit}', [BikefitController::class, 'show'])->name('bikefit.show')->scopeBindings();
        Route::get('bikefit/{bikefit}/edit', [BikefitController::class, 'edit'])->name('bikefit.edit')->scopeBindings();
        Route::put('bikefit/{bikefit}', [BikefitController::class, 'update'])->name('bikefit.update')->scopeBindings();
        Route::post('bikefit/{bikefit}/upload', [BikefitController::class, 'upload'])->name('bikefit.upload')->scopeBindings();
    // Bikefit verslag-, pdf- en download-routes volledig verwijderd
        Route::delete('bikefit/{bikefit}', [BikefitController::class, 'destroy'])->name('bikefit.destroy')->scopeBindings();
        Route::post('bikefit/{bikefit}/duplicate', [BikefitController::class, 'duplicate'])->name('bikefit.duplicate')->scopeBindings();
    // Bikefit image upload routes volledig verwijderd
    });

    // Report template routes volledig verwijderd


    // Inspanningstest routes
    Route::group(['prefix' => 'klanten/{klant}'], function () {
        Route::get('inspanningstest/{test}/results', [InspanningstestController::class, 'results'])->name('inspanningstest.results');
        Route::post('inspanningstest/{test}/generate-report', [InspanningstestController::class, 'generateReport'])->name('inspanningstest.generateReport');
        Route::get('inspanningstest/nieuw', [InspanningstestController::class, 'create'])->name('inspanningstest.create');
        Route::post('inspanningstest', [InspanningstestController::class, 'store'])->name('inspanningstest.store');
        Route::get('inspanningstest/{test}', [InspanningstestController::class, 'show'])->name('inspanningstest.show')->scopeBindings();
        Route::get('inspanningstest/{test}/edit', [InspanningstestController::class, 'edit'])->name('inspanningstest.edit')->scopeBindings();
        Route::put('inspanningstest/{test}', [InspanningstestController::class, 'update'])->name('inspanningstest.update')->scopeBindings();
        Route::get('inspanningstest/{test}/report', [InspanningstestController::class, 'report'])->name('inspanningstest.report')->scopeBindings();
        Route::delete('inspanningstest/{test}', [InspanningstestController::class, 'destroy'])->name('inspanningstest.destroy')->scopeBindings();
        // Authenticated download route for stored inspanningstest PDFs
        Route::get('inspanningstest/{test}/download-report', [\App\Http\Controllers\ReportDownloadController::class, 'downloadInspanningstestReport'])->name('inspanningstest.report.download')->scopeBindings();
        Route::post('inspanningstest/{test}/duplicate', [InspanningstestController::class, 'duplicate'])->name('inspanningstest.duplicate')->scopeBindings();
        Route::post('inspanningstest/{test}/pdf', [InspanningstestController::class, 'pdf'])->name('inspanningstest.pdf')->scopeBindings();
        // Local-only test route: generate & save a minimal PDF for a given inspanningstest
        Route::get('inspanningstest/{test}/generate-test-pdf', function (\App\Models\Klant $klant, \App\Models\Inspanningstest $test) {
            if (!app()->environment('local')) abort(404);
            if ($test->klant_id !== $klant->id) abort(404);

            // Render the existing report preview view to PDF and save to public storage
            try {
                $html = view('inspanningstest.show', ['klant' => $klant, 'test' => $test])->render();
                $pdf = \PDF::loadHTML($html);
                $path = 'reports/' . $klant->id . '/inspanningstest_' . $test->id . '_report.pdf';
                $full = storage_path('app/public/' . $path);
                \Illuminate\Support\Facades\File::ensureDirectoryExists(dirname($full));
                $pdf->save($full);
                return redirect()->back()->with('success', 'Test-PDF gegenereerd: ' . $path);
            } catch (\Throwable $e) {
                \Log::error('Test PDF generatie mislukt: ' . $e->getMessage());
                return redirect()->back()->with('error', 'Kon test-PDF niet genereren: ' . $e->getMessage());
            }
        })->name('inspanningstest.test.generate')->scopeBindings();
    });

    // Export routes
    Route::get('medewerkers-export', fn() => Excel::download(new MedewerkersExport, 'medewerkers.xlsx'))->name('medewerkers.export');
    Route::get('klanten-export', fn() => Excel::download(new KlantenExport, 'klanten.xlsx'))->name('klanten.export');

    // 'Mijn profiel' route: altijd naar profiel bewerken
    Route::get('/mijn-profiel', function () {
        return redirect()->route('profile.edit');
    })->name('mijnprofiel');
});

// Tijdelijke mailtest route voor SMTP-debug
Route::get('/mailtest', function () {
    try {
        \Mail::raw('SMTP test vanuit Bonamiapp', function($message) {
            $message->to('test@bonami-sportcoaching.be')->subject('SMTP Test');
        });
        return 'Mail verzonden! Controleer je inbox.';
    } catch (Exception $e) {
        return 'Mailfout: ' . $e->getMessage();
    }
});

require __DIR__.'/auth.php';

// Sjablonen routes
Route::middleware(['auth'])->group(function () {
    // We verwijderen deze duplicate route
    // Route::resource('sjablonen', App\Http\Controllers\SjablonenController::class);
});

// Temporary debug route: render profile as user id 1 (only for local debugging)
Route::get('/_debug_profile_render', function () {
    if (app()->environment() !== 'local') abort(404);
    auth()->loginUsingId(1);
    return view('profile.edit', ['user' => auth()->user()]);
});

// Dynamic favicon that crops the logo to a centered square and resizes for crisp display
Route::get('/favicon.png', function (Request $request) {
    $size = (int) $request->query('s', 32);
    $size = max(16, min(256, $size));

    $candidates = [
        public_path('logo_bonami_mail.png'),
        public_path('logo_bonami.png'),
    ];
    $srcPath = null;
    foreach ($candidates as $c) {
        if (file_exists($c)) { $srcPath = $c; break; }
    }
    if (!$srcPath) {
        abort(404);
    }

    try {
        if (!function_exists('imagecreatefrompng')) {
            // Fallback: serve original
            return response()->file($srcPath, [
                'Cache-Control' => 'public, max-age=604800'
            ]);
        }

        $src = imagecreatefrompng($srcPath);
        if (!$src) {
            return response()->file($srcPath, [
                'Cache-Control' => 'public, max-age=604800'
            ]);
        }
        imagesavealpha($src, true);
        $w = imagesx($src);
        $h = imagesy($src);
        $side = min($w, $h);
        $x = (int) (($w - $side) / 2);
        $y = (int) (($h - $side) / 2);

        // Crop to square
        $crop = imagecreatetruecolor($side, $side);
        imagesavealpha($crop, true);
        $transparent = imagecolorallocatealpha($crop, 0, 0, 0, 127);
        imagefill($crop, 0, 0, $transparent);
        imagecopy($crop, $src, 0, 0, $x, $y, $side, $side);

        // Resize to requested size
        $dest = imagecreatetruecolor($size, $size);
        imagesavealpha($dest, true);
        $transparent2 = imagecolorallocatealpha($dest, 0, 0, 0, 127);
        imagefill($dest, 0, 0, $transparent2);
        imagecopyresampled($dest, $crop, 0, 0, 0, 0, $size, $size, $side, $side);

        ob_start();
        imagepng($dest);
        $png = ob_get_clean();

        imagedestroy($src);
        imagedestroy($crop);
        imagedestroy($dest);

        return response($png, 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'public, max-age=604800',
        ]);
    } catch (\Throwable $e) {
        return response()->file($srcPath, [
            'Cache-Control' => 'public, max-age=604800'
        ]);
    }
});

// User uploads (drag & drop) - store metadata in DB and file on disk
Route::post('/uploads', [\App\Http\Controllers\UserUploadController::class, 'store'])->middleware('auth')->name('uploads.store');

// Simple browser test form for uploads (only enabled in local env)
Route::get('/uploads', function () {
    if (!app()->environment('local')) {
        abort(404);
    }
    return view('uploads.form');
})->middleware('auth')->name('uploads.form');


// Sjabloonbeheer - DISABLED TO TEST CONFLICT
// Route::resource('templates', App\Http\Controllers\SjablonenController::class);
// Route::get('templates/{template}/editor', [App\Http\Controllers\SjablonenController::class, 'editor'])->name('templates.editor');
// Route::post('templates/{template}/duplicate', [App\Http\Controllers\SjablonenController::class, 'duplicate'])->name('templates.duplicate');

// Template variables/keys route
Route::get('/template-variables', function() {
    return response()->json([
        'bikefit_keys' => [
            '$klant.naam$' => 'Naam van de klant',
            '$klant.voornaam$' => 'Voornaam van de klant', 
            '$klant.email$' => 'Email van de klant',
            '$klant.geboortedatum$' => 'Geboortedatum',
            '$klant.sport$' => 'Sport van de klant',
            '$klant.niveau$' => 'Niveau van de klant',
            '$bikefit.datum$' => 'Datum van de bikefit',
            '{{bikefit.testtype}}' => 'Type van de bikefit test',
            '{{bikefit.lengte_cm}}' => 'Lengte in cm',
            '{{bikefit.binnenbeenlengte_cm}}' => 'Binnenbeenlengte in cm',
            '{{bikefit.armlengte_cm}}' => 'Armlengte in cm',
            '{{bikefit.romplengte_cm}}' => 'Romplengte in cm',
            '{{bikefit.schouderbreedte_cm}}' => 'Schouderbreedte in cm',
            '{{bikefit.zadel_trapas_hoek}}' => 'Zadel-trapas hoek',
            '{{bikefit.zadel_trapas_afstand}}' => 'Zadel-trapas afstand',
            '{{bikefit.stuur_trapas_hoek}}' => 'Stuur-trapas hoek',
            '{{bikefit.stuur_trapas_afstand}}' => 'Stuur-trapas afstand',
            '{{bikefit.aanpassingen_zadel}}' => 'Zadel aanpassingen',
            '{{bikefit.aanpassingen_setback}}' => 'Setback aanpassingen',
            '{{bikefit.aanpassingen_reach}}' => 'Reach aanpassingen',
            '{{bikefit.aanpassingen_drop}}' => 'Drop aanpassingen',
            '{{bikefit.type_zadel}}' => 'Type zadel',
            '{{bikefit.zadeltil}}' => 'Zadeltil',
            '{{bikefit.zadelbreedte}}' => 'Zadelbreedte',
            '{{bikefit.fietsmerk}}' => 'Fietsmerk',
            '{{bikefit.kadermaat}}' => 'Kadermaat',
            '{{bikefit.frametype}}' => 'Frametype',
            '{{bikefit.algemene_klachten}}' => 'Algemene klachten',
            '{{bikefit.opmerkingen}}' => 'Opmerkingen',
            '$mobiliteitstabel$' => 'Complete mobiliteitstabel (geformatteerd)',
            '$MobiliteitTabel$' => 'Oude mobiliteitstabel versie',
            '$mobility_table$' => 'Nieuwe mobiliteitstabel (Engels)',
            '$mobiliteitstabel_nieuw$' => 'Nieuwe verbeterde mobiliteitstabel',
            '$mobility_table_report$' => 'Perfecte mobiliteitstabel met kleurenbalk (DEZE!)',
            '$_mobility_table$' => 'Alternatieve mobiliteitstabel versie 1',
            '$mobility_results$' => 'Uitgebreide mobiliteit resultaten',
            '$_mobility_table_report$' => 'Report versie mobiliteitstabel',
            '$_mobility_results$' => 'Uitgebreide mobility results versie',
            '$flexibiliteitstabel$' => 'Flexibiliteitstabel (alternatief)',
            '$bewegingstabel$' => 'Bewegingstabel',
            '$mobiliteit_overzicht$' => 'Mobiliteit overzicht tabel',
            '$ROM_tabel$' => 'Range of Motion tabel',
            '$flexibiliteit_resultaten$' => 'Flexibiliteit resultaten tabel',
            '$mobiliteit_tabel_html$' => 'Mobiliteit tabel HTML (CORRECTE SLEUTEL!)',
            '{{bikefit.straight_leg_raise_links}}' => 'Straight Leg Raise Links',
            '{{bikefit.straight_leg_raise_rechts}}' => 'Straight Leg Raise Rechts',
            '{{bikefit.knieflexie_links}}' => 'Knieflexie Links',
            '{{bikefit.knieflexie_rechts}}' => 'Knieflexie Rechts',
            '{{bikefit.heup_endorotatie_links}}' => 'Heup Endorotatie Links',
            '{{bikefit.heup_endorotatie_rechts}}' => 'Heup Endorotatie Rechts',
            '{{bikefit.heup_exorotatie_links}}' => 'Heup Exorotatie Links', 
            '{{bikefit.heup_exorotatie_rechts}}' => 'Heup Exorotatie Rechts',
            '{{bikefit.enkeldorsiflexie_links}}' => 'Enkeldorsiflexie Links',
            '{{bikefit.enkeldorsiflexie_rechts}}' => 'Enkeldorsiflexie Rechts',
            '{{bikefit.one_leg_squat_links}}' => 'One Leg Squat Links',
            '{{bikefit.one_leg_squat_rechts}}' => 'One Leg Squat Rechts',
        ]
    ]);
})->name('template.variables');

// Admin routes
Route::middleware(['auth', 'verified'])->group(function() {
    Route::get('/admin/staff-notes/overview', function() {
        \Log::info('Route binnenkomst admin.staffnotes.overview', [
            'user' => auth()->user(),
            'user_id' => optional(auth()->user())->id,
            'is_authenticated' => auth()->check(),
            'request_ip' => request()->ip(),
        ]);
        return app(\App\Http\Controllers\StaffNoteController::class)->index();
    })
        // ->middleware('can:admin') // Tijdelijk uitgeschakeld
        ->name('admin.staffnotes.overview');
        
    // Birthday Management
    Route::prefix('admin/birthdays')->name('admin.birthdays.')->group(function() {
        Route::get('/', [App\Http\Controllers\BirthdayController::class, 'index'])->name('index');
        Route::post('/send-manual', [App\Http\Controllers\BirthdayController::class, 'sendManual'])->name('send.manual');
    });
    
    // Quick access to birthday management
    Route::get('/birthdays', [App\Http\Controllers\BirthdayController::class, 'index'])->name('birthdays.index');
});

// Admin index route - CRITICAL FOR SITE TO WORK
Route::get('/admin', function() {
    return view('admin.index');
})->name('admin.index');

// Testzadels management - Complete CRUD systeem
Route::middleware(['auth', 'verified'])->prefix('testzadels')->name('testzadels.')->group(function () {
    Route::get('/', [App\Http\Controllers\TestzadelsController::class, 'index'])->name('index');
    Route::get('/create', [App\Http\Controllers\TestzadelsController::class, 'create'])->name('create');
    Route::post('/', [App\Http\Controllers\TestzadelsController::class, 'store'])->name('store');
    Route::get('/archived', [App\Http\Controllers\TestzadelsController::class, 'archived'])->name('archived');
    Route::get('/{testzadel}', [App\Http\Controllers\TestzadelsController::class, 'show'])->name('show');
    Route::get('/{testzadel}/edit', [App\Http\Controllers\TestzadelsController::class, 'edit'])->name('edit');
    Route::put('/{testzadel}', [App\Http\Controllers\TestzadelsController::class, 'update'])->name('update');
    Route::delete('/{testzadel}', [App\Http\Controllers\TestzadelsController::class, 'destroy'])->name('destroy');
    
    // Extra acties
    Route::post('/{testzadel}/archive', [App\Http\Controllers\TestzadelsController::class, 'archive'])->name('archive');
    Route::post('/{testzadel}/reminder', [App\Http\Controllers\TestzadelsController::class, 'sendReminder'])->name('reminder');
    Route::post('/{testzadel}/returned', [App\Http\Controllers\TestzadelsController::class, 'markAsReturned'])->name('returned');
    Route::post('/bulk-reminders', [App\Http\Controllers\TestzadelsController::class, 'sendBulkReminders'])->name('bulk-reminders');
});

// Test route voor Browsershot
Route::get('/test-browsershot', [\App\Http\Controllers\PdfController::class, 'testBrowsershot'])->name('test.browsershot');

// Bikefit uploads
Route::middleware(['auth', 'verified'])->group(function() {
    Route::get('uploads/{upload}', [App\Http\Controllers\BikefitController::class, 'serveUpload'])->name('uploads.show');
    Route::delete('uploads/{upload}', [\App\Http\Controllers\UploadController::class, 'destroy'])->name('uploads.destroy');
});

// PDF route toegevoegd
Route::get('klanten/{klantId}/bikefit/{bikefitId}/generate-pdf', [App\Http\Controllers\BikefitController::class, 'generatePdf'])->name('bikefit.generate-pdf');

// Test stuurpen debug route
Route::post('/test-stuurpen', function(Request $request) {
    \Log::info('Test stuurpen debug:', [
        'all_input' => $request->all(),
        'has_pre' => $request->has('aanpassingen_stuurpen_pre'),
        'has_post' => $request->has('aanpassingen_stuurpen_post'),
        'has_aan' => $request->has('aanpassingen_stuurpen_aan'),
        'input_pre' => $request->input('aanpassingen_stuurpen_pre'),
        'input_post' => $request->input('aanpassingen_stuurpen_post'),
        'input_aan' => $request->input('aanpassingen_stuurpen_aan'),
    ]);
    
    return response()->json(['success' => true]);
})->name('test.stuurpen');

// Test route voor klanten import
Route::get('/test-import', function() {
    return 'Import route werkt!';
})->name('test.import');

// Import routes op een andere URL structuur
Route::get('/import/klanten', [\App\Http\Controllers\KlantenController::class, 'showImport'])->name('klanten.import.form');
Route::post('/import/klanten', [\App\Http\Controllers\KlantenController::class, 'import'])->name('klanten.import');
Route::get('/import/klanten/template', [\App\Http\Controllers\KlantenController::class, 'downloadTemplate'])->name('klanten.template');

// Bikefit import routes
Route::get('/import/bikefits', [\App\Http\Controllers\BikefitController::class, 'showImport'])->name('bikefit.import.form');
Route::post('/import/bikefits', [\App\Http\Controllers\BikefitController::class, 'importBikefits'])->name('bikefit.import');
Route::get('/import/bikefits/template', [\App\Http\Controllers\BikefitController::class, 'downloadBikefitTemplate'])->name('bikefit.template');

// Export routes
Route::get('/export/klanten', [\App\Http\Controllers\KlantenController::class, 'exportKlanten'])->name('klanten.export');
Route::get('/export/bikefits', [\App\Http\Controllers\BikefitController::class, 'exportBikefits'])->name('bikefits.export');

// Klanten routes
Route::middleware('auth')->group(function () {
    // Import routes moeten VOOR resource routes staan
    Route::get('/klanten/import', [\App\Http\Controllers\KlantenController::class, 'showImport'])->name('klanten.import.form');
    Route::post('/klanten/import', [\App\Http\Controllers\KlantenController::class, 'import'])->name('klanten.import');
    Route::get('/klanten/template', [\App\Http\Controllers\KlantenController::class, 'downloadTemplate'])->name('klanten.template');
    
    // Resource routes
    Route::resource('klanten', \App\Http\Controllers\KlantenController::class);
});

// Add this route to your existing web routes
Route::post('/api/calculate-thresholds', [App\Http\Controllers\ThresholdCalculationController::class, 'calculateThresholds'])
    ->middleware('auth')
    ->name('api.calculate-thresholds');

// API route for fetching klant bikefits
Route::get('/api/klanten/{klant}/bikefits', function($klantId) {
    $bikefits = \App\Models\Bikefit::where('klant_id', $klantId)
        ->select('id', 'datum', 'testtype', 'type_zadel', 'zadelbreedte')
        ->orderBy('datum', 'desc')
        ->get()
        ->map(function($bikefit) {
            return [
                'id' => $bikefit->id,
                'datum' => $bikefit->datum->format('d/m/Y'),
                'testtype' => $bikefit->testtype,
                'type_zadel' => $bikefit->type_zadel,
                'zadelbreedte' => $bikefit->zadelbreedte
            ];
        });
    
    return response()->json($bikefits);
})->middleware('auth')->name('api.klant.bikefits');

// Quick admin user creation route (local environment only)
Route::get('/create-admin', function () {
    if (!app()->environment('local')) {
        abort(404, 'Only available in local environment');
    }
    
    try {
        // Check if admin user already exists
        $existingAdmin = \App\Models\User::where('email', 'info@bonami-sportcoaching.be')->first();
        
        if ($existingAdmin) {
            // Update existing user to ensure admin privileges
            $existingAdmin->update([
                'name' => 'Bonami Admin',
                'email' => 'info@bonami-sportcoaching.be',
                'password' => \Hash::make('password'),
                'email_verified_at' => now(),
            ]);
            
            return response()->json([
                'success' => true,
                'message' => '✅ Admin user UPDATED successfully!',
                'email' => 'info@bonami-sportcoaching.be',
                'password' => 'password',
                'note' => 'You can now login with full admin access!'
            ]);
        } else {
            // Create new admin user
            $admin = \App\Models\User::create([
                'name' => 'Bonami Admin',
                'email' => 'info@bonami-sportcoaching.be',
                'password' => \Hash::make('password'),
                'email_verified_at' => now(),
            ]);
            
            return response()->json([
                'success' => true,
                'message' => '✅ Admin user CREATED successfully!',
                'email' => 'info@bonami-sportcoaching.be',
                'password' => 'password',
                'user_id' => $admin->id,
                'note' => 'You can now login with full admin access!'
            ]);
        }
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => '❌ Error creating admin user',
            'error' => $e->getMessage()
        ], 500);
    }
})->name('create.admin');

// Add invitation route for klanten
Route::post('/klanten/{klant}/invite', [App\Http\Controllers\KlantenController::class, 'sendInvitation'])->name('klanten.invite')->middleware('auth');
Route::post('/medewerkers/{medewerker}/invite', [App\Http\Controllers\MedewerkerController::class, 'sendInvitation'])->name('medewerkers.invite')->middleware('auth');

// Add medewerker invitation route
Route::post('/medewerkers/{medewerker}/invite', [App\Http\Controllers\MedewerkerController::class, 'sendInvitation'])->name('medewerkers.invite')->middleware('auth');

// Staff Notes routes (legacy)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('staff-notes', App\Http\Controllers\StaffNoteController::class);
    Route::post('/tinymce/upload', [App\Http\Controllers\TinyMCEController::class, 'upload'])->name('tinymce.upload');
});

// Dashboard Content routes (nieuwe systeem)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('dashboard-content', App\Http\Controllers\DashboardContentController::class);
    Route::patch('/dashboard-content/{dashboardContent}/archive', [App\Http\Controllers\DashboardContentController::class, 'archive'])->name('dashboard-content.archive');
    Route::patch('/dashboard-content/{dashboardContent}/restore', [App\Http\Controllers\DashboardContentController::class, 'restore'])->name('dashboard-content.restore');
    Route::get('/dashboard-content-archived', [App\Http\Controllers\DashboardContentController::class, 'archived'])->name('dashboard-content.archived');
    Route::post('/dashboard-content/update-order', [App\Http\Controllers\DashboardContentController::class, 'updateOrder'])->name('dashboard-content.update-order');
});

// Profile Settings Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/instellingen', [App\Http\Controllers\ProfileSettingsController::class, 'index'])->name('profile.settings');
    Route::patch('/instellingen/personal', [App\Http\Controllers\ProfileSettingsController::class, 'updatePersonal'])->name('profile.update.personal');
    Route::post('/instellingen/avatar', [App\Http\Controllers\ProfileSettingsController::class, 'updateAvatar'])->name('profile.update.avatar');
    Route::patch('/instellingen/password', [App\Http\Controllers\ProfileSettingsController::class, 'updatePassword'])->name('profile.update.password');
    Route::patch('/instellingen/preferences', [App\Http\Controllers\ProfileSettingsController::class, 'updatePreferences'])->name('profile.update.preferences');
    Route::post('/instellingen/2fa/toggle', [App\Http\Controllers\ProfileSettingsController::class, 'toggle2FA'])->name('profile.2fa.toggle');
    Route::delete('/instellingen/deactivate', [App\Http\Controllers\ProfileSettingsController::class, 'deactivateAccount'])->name('profile.deactivate');
});


// Direct route to admin database tools (bypassing view conflicts)
Route::middleware(['auth', 'verified'])->group(function() {
    Route::get('/admin/database-tools', function() {
        $notes = \App\Models\StaffNote::with('user')->latest()->paginate(10);
        
        // Explicitly load the view file with database tools
        return view()->file(resource_path('views/admin/staff-notes/overview.blade.php'), compact('notes'));
    })->name('admin.database.tools');
});

// Sjablonen routes - WERKENDE ROUTES HERSTELD!
Route::middleware(['auth'])->group(function () {
    // Testtype route MOET VOOR resource routes staan
    Route::get('sjablonen/testtypes/{categorie}', [\App\Http\Controllers\SjablonenController::class, 'getTesttypes'])->name('sjablonen.testtypes');
    
    // HOOFDROUTE - DEZE WERKTE AL!!!
    Route::resource('sjablonen', \App\Http\Controllers\SjablonenController::class);
    
    // AJAX routes voor sjabloon editor
    Route::post('sjablonen/{sjabloon}/pages/{pagina}/update', [\App\Http\Controllers\SjablonenController::class, 'updatePagina'])->name('sjablonen.pagina.update');
    Route::post('sjablonen/{sjabloon}/pages', [\App\Http\Controllers\SjablonenController::class, 'addPagina'])->name('sjablonen.pagina.add');
    Route::delete('sjablonen/{sjabloon}/pages/{pagina}', [\App\Http\Controllers\SjablonenController::class, 'deletePagina'])->name('sjablonen.pagina.delete');
});

// Rapport routes
Route::middleware(['auth'])->group(function () {
    // Template selectie
    Route::get('rapporten/select-template', [\App\Http\Controllers\RapportController::class, 'selectTemplate'])->name('rapporten.select-template');
    
    // Bikefit rapporten
    Route::get('bikefits/{bikefit}/rapport', [\App\Http\Controllers\RapportController::class, 'bikefitRapport'])->name('rapporten.bikefit');
    Route::get('bikefits/{bikefit}/rapport/pdf', [\App\Http\Controllers\RapportController::class, 'bikefitRapportPdf'])->name('rapporten.bikefit.pdf');
    
    // Inspanningstest rapporten
    Route::get('inspanningstests/{inspanningstest}/rapport', [\App\Http\Controllers\RapportController::class, 'inspanningstestRapport'])->name('rapporten.inspanningstest');
    Route::get('inspanningstests/{inspanningstest}/rapport/pdf', [\App\Http\Controllers\RapportController::class, 'inspanningstestRapportPdf'])->name('rapporten.inspanningstest.pdf');
});

// Simple database fix
Route::middleware(['auth'])->group(function () {
    Route::get('/simple-fix', [App\Http\Controllers\SimpleFixController::class, 'show'])->name('simple.show');
    Route::post('/simple-fix', [App\Http\Controllers\SimpleFixController::class, 'fix'])->name('simple.fix');
    Route::post('/simple-test-save', [App\Http\Controllers\SimpleFixController::class, 'testSave'])->name('simple.test-save');
});

// Debug routes (alleen voor development)
Route::middleware(['auth'])->group(function () {
    // Debug routes - temporarily restored to fix layout errors
    Route::get('/debug/database', function() { 
        return redirect('/'); 
    })->name('debug.database');
    
    // Birthday email testing (local only)
    Route::get('/debug/birthday-test', function() {
        if (!app()->environment('local')) abort(404);
        
        try {
            // Test with first klant
            $testKlant = \App\Models\Klant::whereNotNull('email')->first();
            
            if (!$testKlant) {
                return response()->json([
                    'error' => 'No klant with email found for testing'
                ]);
            }
            
            // Send test birthday email with CORRECT from address
            \Mail::send('emails.birthday', [
                'person' => $testKlant,
                'type' => 'klant'
            ], function($message) use ($testKlant) {
                $message->to($testKlant->email, $testKlant->voornaam . ' ' . $testKlant->naam);
                // Use config values to ensure consistency with .env
                $message->from(config('mail.from.address'), config('mail.from.name'));
                $message->replyTo(config('mail.from.address'), config('mail.from.name'));
                $message->subject('🎂 TEST: Birthday Email - Hiep hiep hoera!');
            });
            
            return response()->json([
                'success' => true,
                'message' => "Test birthday email sent to {$testKlant->email}",
                'klant' => $testKlant->voornaam . ' ' . $testKlant->naam,
                'from_address' => config('mail.from.address'),
                'mail_config' => [
                    'host' => config('mail.host'),
                    'port' => config('mail.port'),
                    'encryption' => config('mail.encryption'),
                    'username' => config('mail.username')
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'mail_config_check' => [
                    'from_address' => config('mail.from.address'),
                    'from_name' => config('mail.from.name'),
                    'host' => config('mail.host'),
                    'username' => config('mail.username')
                ]
            ], 500);
        }
    })->name('debug.birthday.test');
    
    // Preview testzadel reminder email
    Route::get('/debug/testzadel-reminder-preview', function() {
        if (!app()->environment('local')) abort(404);
        
        // Get sample testzadel or create dummy data
        $sampleTestzadel = \App\Models\Testzadel::with('klant')->first() ?? (object)[
            'zadel_merk' => 'TESTZADEL',
            'zadel_model' => 'Pro',
            'zadel_type' => 'Sport',
            'zadel_breedte' => 143,
            'uitleen_datum' => \Carbon\Carbon::parse('2025-09-20'),
            'verwachte_retour_datum' => \Carbon\Carbon::parse('2025-10-05'),
        ];
        
        $sampleKlant = $sampleTestzadel->klant ?? (object)[
            'voornaam' => 'Jan',
            'naam' => 'Janssen',
            'email' => 'jan@example.com'
        ];
        
        return view('emails.testzadel-reminder', [
            'testzadel' => $sampleTestzadel,
            'klant' => $sampleKlant
        ]);
    })->name('debug.testzadel.reminder.preview');
    
    // Test testzadel reminder email
    Route::get('/debug/testzadel-reminder-test', function() {
        if (!app()->environment('local')) abort(404);
        
        try {
            // Get first testzadel with klant
            $testzadel = \App\Models\Testzadel::with('klant')->whereHas('klant', function($q) {
                $q->whereNotNull('email');
            })->first();
            
            if (!$testzadel) {
                return response()->json([
                    'error' => 'No testzadel with klant email found for testing'
                ]);
            }
            
            // Send test reminder email
            \Mail::send('emails.testzadel-reminder', [
                'testzadel' => $testzadel,
                'klant' => $testzadel->klant
            ], function($message) use ($testzadel) {
                $message->to($testzadel->klant->email, $testzadel->klant->voornaam . ' ' . $testzadel->klant->naam);
                $message->from(config('mail.from.address'), config('mail.from.name'));
                $message->replyTo(config('mail.from.address'), config('mail.from.name'));
                $message->subject('🧪 TEST: Herinnering Testzadel - Bonami Sportcoaching');
            });
            
            return response()->json([
                'success' => true,
                'message' => "Test testzadel reminder sent to {$testzadel->klant->email}",
                'testzadel' => $testzadel->zadel_merk . ' ' . $testzadel->zadel_model,
                'klant' => $testzadel->klant->voornaam . ' ' . $testzadel->klant->naam
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    })->name('debug.testzadel.reminder.test');
    
    // Check mail configuration
    Route::get('/debug/mail-config', function() {
        if (!app()->environment('local')) abort(404);
        
        return response()->json([
            'mail_config' => [
                'mailer' => config('mail.default'),
                'host' => config('mail.mailers.smtp.host'),
                'port' => config('mail.mailers.smtp.port'),
                'encryption' => config('mail.mailers.smtp.encryption'),
                'username' => config('mail.mailers.smtp.username'),
                'from_address' => config('mail.from.address'),
                'from_name' => config('mail.from.name'),
            ],
            'env_values' => [
                'MAIL_MAILER' => env('MAIL_MAILER'),
                'MAIL_HOST' => env('MAIL_HOST'),
                'MAIL_PORT' => env('MAIL_PORT'),
                'MAIL_USERNAME' => env('MAIL_USERNAME'),
                'MAIL_ENCRYPTION' => env('MAIL_ENCRYPTION'),
                'MAIL_FROM_ADDRESS' => env('MAIL_FROM_ADDRESS'),
                'MAIL_FROM_NAME' => env('MAIL_FROM_NAME'),
            ]
        ], 200, [], JSON_PRETTY_PRINT);
    })->name('debug.mail.config');
        
    // Check today's birthdays
    Route::get('/debug/birthday-check', function() {
        if (!app()->environment('local')) abort(404);
        
        $today = \Carbon\Carbon::today();
        
        // Check Klanten
        $klanten = \App\Models\Klant::whereRaw('DATE_FORMAT(geboortedatum, "%m-%d") = ?', [$today->format('m-d')])
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->get();
        
        // Check Medewerkers  
        $medewerkers = \App\Models\Medewerker::whereRaw('DATE_FORMAT(geboortedatum, "%m-%d") = ?', [$today->format('m-d')])
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->get();
        
        return response()->json([
            'today' => $today->format('d/m/Y'),
            'klanten_birthdays' => $klanten->map(function($k) {
                return [
                    'name' => $k->voornaam . ' ' . $k->naam,
                    'email' => $k->email,
                    'birthdate' => $k->geboortedatum?->format('d/m/Y')
                ];
            }),
            'medewerkers_birthdays' => $medewerkers->map(function($m) {
                return [
                    'name' => $m->voornaam . ' ' . $m->naam,
                    'email' => $m->email,
                    'birthdate' => $m->geboortedatum?->format('d/m/Y')
                ];
            }),
            'total_birthdays' => $klanten->count() + $medewerkers->count()
        ], 200, [], JSON_PRETTY_PRINT);
        
    })->name('debug.birthday.check');
    
    // Testzadels database check route
    Route::get('/debug/testzadels-check', function() {
        if (!app()->environment('local')) abort(404);
        
        try {
            // Check if table exists and what columns it has
            $tableExists = Schema::hasTable('testzadels');
            $columns = [];
            
            if ($tableExists) {
                $columns = Schema::getColumnListing('testzadels');
            }
            
            $requiredColumns = [
                'id', 'klant_id', 'bikefit_id', 'merk', 'model', 'type', 'breedte', 
                'foto_pad', 'uitgeleend_op', 'verwachte_terugbring_datum', 
                'werkelijke_terugbring_datum', 'status', 'beschrijving', 'opmerkingen', 
                'gearchiveerd', 'gearchiveerd_op', 'laatste_herinnering', 'created_at', 'updated_at'
            ];
            
            $missingColumns = array_diff($requiredColumns, $columns);
            
            return response()->json([
                'table_exists' => $tableExists,
                'existing_columns' => $columns,
                'required_columns' => $requiredColumns,
                'missing_columns' => $missingColumns,
                'status' => empty($missingColumns) ? 'OK' : 'MISSING_COLUMNS'
            ], 200, [], JSON_PRETTY_PRINT);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'status' => 'ERROR'
            ], 500, [], JSON_PRETTY_PRINT);
        }
    })->name('debug.testzadels.check');
    
    // Quick GET route to fix status column
    Route::get('/debug/testzadels-fix-status', function() {
        if (!app()->environment('local')) abort(404);
        
        try {
            // Fix status column length
            DB::statement("ALTER TABLE testzadels MODIFY COLUMN status VARCHAR(50)");
            
            return response()->json([
                'success' => true,
                'message' => 'Status column fixed! You can now mark testzadels as "teruggegeven".'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    })->name('debug.testzadels.fix.status');
    
    // Manual SQL fix for testzadels table
    Route::post('/debug/testzadels-fix', function() {
        if (!app()->environment('local')) abort(404);
        
        try {
            $existingColumns = Schema::getColumnListing('testzadels');
            $addedColumns = [];
            
            // List of columns to add with their SQL
            $columnsToAdd = [
                'model' => "ALTER TABLE testzadels ADD COLUMN model VARCHAR(255) NOT NULL DEFAULT '' AFTER merk",
                'type' => "ALTER TABLE testzadels ADD COLUMN type VARCHAR(255) NULL AFTER model",
                'foto_pad' => "ALTER TABLE testzadels ADD COLUMN foto_pad VARCHAR(255) NULL AFTER breedte",
                'beschrijving' => "ALTER TABLE testzadels ADD COLUMN beschrijving TEXT NULL",
                'gearchiveerd' => "ALTER TABLE testzadels ADD COLUMN gearchiveerd TINYINT(1) NOT NULL DEFAULT 0",
                'gearchiveerd_op' => "ALTER TABLE testzadels ADD COLUMN gearchiveerd_op DATETIME NULL",
                'laatste_herinnering' => "ALTER TABLE testzadels ADD COLUMN laatste_herinnering DATETIME NULL"
            ];
            
            foreach ($columnsToAdd as $columnName => $sql) {
                if (!in_array($columnName, $existingColumns)) {
                    try {
                        DB::statement($sql);
                        $addedColumns[] = $columnName;
                    } catch (\Exception $e) {
                        return response()->json([
                            'error' => "Failed to add column $columnName: " . $e->getMessage()
                        ], 500);
                    }
                }
            }
            
            // Fix status column length
            try {
                DB::statement("ALTER TABLE testzadels MODIFY COLUMN status VARCHAR(50)");
                $addedColumns[] = 'status_length_fixed';
            } catch (\Exception $e) {
                // Column might already be correct size
            }
            
            return response()->json([
                'success' => true,
                'added_columns' => $addedColumns,
                'message' => 'Database structure fixed!'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    })->name('debug.testzadels.fix');
    
    // Route::post('/debug/test-write', [App\Http\Controllers\DebugController::class, 'testWrite'])->name('debug.test-write');
    // Route::post('/debug/fix-database', [App\Http\Controllers\DebugController::class, 'fixDatabase'])->name('debug.fix-database');
    // Route::post('/debug/add-address-fields', [App\Http\Controllers\DebugController::class, 'addAddressFields'])->name('debug.add-address-fields');
});

// Email management routes - STEP 7: Add triggers and logs functionality
Route::middleware(['auth'])->group(function () {
    Route::get('/admin/email', [\App\Http\Controllers\Admin\EmailController::class, 'index'])->name('admin.email.index');
    Route::get('/admin/email/templates', [\App\Http\Controllers\Admin\EmailController::class, 'templates'])->name('admin.email.templates');
    Route::get('/admin/email/templates/create', [\App\Http\Controllers\Admin\EmailController::class, 'createTemplate'])->name('admin.email.templates.create');
    Route::post('/admin/email/templates', [\App\Http\Controllers\Admin\EmailController::class, 'storeTemplate'])->name('admin.email.templates.store');
    Route::get('/admin/email/templates/{id}/edit', [\App\Http\Controllers\Admin\EmailController::class, 'editTemplate'])->name('admin.email.templates.edit');
    Route::put('/admin/email/templates/{id}', [\App\Http\Controllers\Admin\EmailController::class, 'updateTemplate'])->name('admin.email.templates.update');
    Route::delete('/admin/email/templates/{id}', [\App\Http\Controllers\Admin\EmailController::class, 'destroyTemplate'])->name('admin.email.templates.destroy');
    Route::get('/admin/email/settings', [\App\Http\Controllers\Admin\EmailController::class, 'settings'])->name('admin.email.settings');
    Route::post('/admin/email/settings', [\App\Http\Controllers\Admin\EmailController::class, 'updateSettings'])->name('admin.email.settings.update');
    
    // New trigger and logs routes
    Route::get('/admin/email/triggers', [\App\Http\Controllers\Admin\EmailController::class, 'triggers'])->name('admin.email.triggers');
    Route::get('/admin/email/triggers/{id}/edit', [\App\Http\Controllers\Admin\EmailController::class, 'editTrigger'])->name('admin.email.triggers.edit');
    Route::put('/admin/email/triggers/{id}', [\App\Http\Controllers\Admin\EmailController::class, 'updateTrigger'])->name('admin.email.triggers.update');
    Route::get('/admin/email/logs', [\App\Http\Controllers\Admin\EmailController::class, 'logs'])->name('admin.email.logs');
    Route::post('/admin/email/test-triggers', [\App\Http\Controllers\Admin\EmailController::class, 'testTriggers'])->name('admin.email.test-triggers');
    Route::post('/admin/email/setup-triggers', [\App\Http\Controllers\Admin\EmailController::class, 'setupTriggers'])->name('admin.email.setup-triggers');
    Route::post('/admin/email/migrate-templates', [\App\Http\Controllers\Admin\EmailController::class, 'migrateTemplates'])->name('admin.email.migrate-templates');
});

// Avatar management routes - TEMP WITHOUT AUTH MIDDLEWARE
Route::post('/avatar/upload', [\App\Http\Controllers\AvatarController::class, 'upload'])->name('avatar.upload');
Route::delete('/avatar/delete', [\App\Http\Controllers\AvatarController::class, 'delete'])->name('avatar.delete');
Route::post('/klanten/{klant}/avatar', [\App\Http\Controllers\AvatarController::class, 'uploadForKlant'])->name('klanten.avatar');

// WERKENDE OPLOSSING - GEBRUIK /template-manager IN PLAATS VAN /templates
Route::get('/template-manager', function() {
    return redirect()->route('sjablonen.index');
})->middleware('auth')->name('template.manager');

// HERNOEM KAPOTTE ROUTE NAAR /template (was /templates)
Route::get('/template', function() {
    return 'OUDE TEMPLATES ROUTE WERKT!!! Nu hernoemd naar /template!';
});

// TEST MET ANDERE NAAM
Route::get('/sjabloon-test', function() {
    return 'SJABLOON-TEST ROUTE WERKT!!! Dit is NIET /templates!';
});
