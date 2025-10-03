<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Klant;
use App\Models\Bikefit;
use App\Models\Inspanningstest;

class ReportDownloadController extends Controller
{
    public function downloadBikefitReport(Klant $klant, Bikefit $bikefit)
    {
        // ensure the bikefit belongs to the klant
        if ($bikefit->klant_id !== $klant->id) {
            abort(404);
        }

        $path = 'reports/' . ($klant->id ?? 'unknown') . '/bikefit_' . $bikefit->id . '_report.pdf';
        if (!\Storage::disk('public')->exists($path)) {
            abort(404);
        }

        $full = \Storage::disk('public')->path($path);
        return response()->download($full, basename($full), [
            'Content-Type' => 'application/pdf'
        ]);
    }

    public function downloadInspanningstestReport(Klant $klant, $testId)
    {
        // Manually resolve the Inspanningstest to avoid scoped binding trying to call a non-existing relation on Klant
        $test = Inspanningstest::where('id', $testId)->where('klant_id', $klant->id)->first();
        if (!$test) {
            abort(404);
        }

        $path = 'reports/' . ($klant->id ?? 'unknown') . '/inspanningstest_' . $test->id . '_report.pdf';
        if (!\Storage::disk('public')->exists($path)) {
            abort(404);
        }

        $full = \Storage::disk('public')->path($path);
        return response()->download($full, basename($full), [
            'Content-Type' => 'application/pdf'
        ]);
    }
}
