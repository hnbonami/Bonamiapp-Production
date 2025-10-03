<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Announcement;

class AnnouncementController extends Controller
{
    public function index()
    {
        $announcement = Announcement::latest()->first();
        return response()->json($announcement);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'content' => 'required|string',
        ]);
        $announcement = Announcement::latest()->first();
        if (!$announcement) {
            $announcement = Announcement::create([
                'content' => $data['content'],
                'is_new' => true,
            ]);
        } else {
            $announcement->update([
                'content' => $data['content'],
                'is_new' => true,
            ]);
        }
        return response()->json($announcement);
    }

    public function markRead()
    {
        $announcement = Announcement::latest()->first();
        if ($announcement) {
            $announcement->is_new = false;
            $announcement->save();
        }
        return response()->json(['success' => true]);
    }
}
