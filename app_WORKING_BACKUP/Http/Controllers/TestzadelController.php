<?php

namespace App\Http\Controllers;

use App\Models\Bikefit;
use Illuminate\Http\Request;

class TestzadelController extends Controller
{
    public function index()
    {
        // Oude data van bikefits
        $testzadels = Bikefit::with(['klant'])
            ->whereNotNull('nieuw_testzadel')
            ->where('nieuw_testzadel', '!=', '')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Statistieken voor nieuwe view (tijdelijk met oude data)
        $statistieken = [
            'uitgeleend' => $testzadels->count(),
            'laat' => 0, // Kan niet berekend worden met oude data
            'needs_reminder' => 0,
            'vandaag_terug' => 0
        ];

        return view('testzadels.index', compact('testzadels', 'statistieken'));
    }
}