<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Testzadel;
use App\Models\Klant;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class TestzadelsController extends Controller
{
    public function index()
    {
        $testzadels = Testzadel::with('klant')->orderBy('created_at', 'desc')->get();
        return view('testzadels.index', compact('testzadels'));
    }

    public function sendReminder(Testzadel $testzadel)
    {
        // Update reminder timestamps
        $testzadel->update([
            'herinnering_verstuurd_op' => now(),
            'laatste_herinnering' => now(),
        ]);

        // Send email using new template system
        $success = \App\Helpers\MailHelper::sendTestzadelReminder($testzadel, $testzadel->klant);

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => '✅ Testzadel herinnering verstuurd via nieuwe template!'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => '❌ Fout bij versturen herinnering'
            ]);
        }
    }

    // Add other methods as needed...
}