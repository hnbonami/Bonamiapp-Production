<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Admin dashboard - alleen voor admins en superadmins
     */
    public function index()
    {
        // Check admin toegang - alleen admin, organisatie_admin en superadmin
        if (!in_array(auth()->user()->role, ['admin', 'organisatie_admin', 'superadmin'])) {
            abort(403, 'Geen toegang. Alleen administrators hebben toegang tot het admin dashboard.');
        }
        
        return view('admin.index');
    }
}