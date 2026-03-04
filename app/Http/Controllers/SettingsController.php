<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Album; // Siguraduhing naka-import ang Album model

class SettingsController extends Controller
{
    public function index()
    {
        // 1. Kunin ang lahat ng Albums para sa selection sa settings
        $albums = Album::orderBy('name')->get();

        // 2. Kunin ang kasalukuyang settings mula sa database para mai-display sa form
        $settings = DB::table('settings')->pluck('value', 'key');

        return view('admin.settings.list', [
            'title' => 'Slideshow Settings',
            'albums' => $albums,
            'settings' => $settings,
        ]);
    }

    public function getLatestData() 
    {
        // Fetch settings para sa real-time refresh ng slideshow
        $settings = DB::table('settings')
            ->whereIn('key', ['slide_duration', 'transition_effect'])
            ->pluck('value', 'key');

        return response()->json([
            'seconds' => (int)($settings['slide_duration'] ?? 5),
            'effect' => $settings['transition_effect'] ?? 'fade',
            // Ginagamit ito para malaman ng slideshow kung kailangan mag-reload
            'last_update' => DB::table('settings')->max('updated_at') ?? Carbon::now()->toDateTimeString(),
        ]);
    }

    public function update(Request $request)
    {
        // Validation base sa requirement ng slideshow
        $request->validate([
            'slide_duration' => 'required|integer|min:1|max:60',
            'transition_effect' => 'required|string',
            'display_album_ids' => 'nullable|array', // Mas mainam kung array ang galing sa form
        ]);

        $data = [
            'slide_duration' => $request->slide_duration,
            'transition_effect' => $request->transition_effect,
            // I-convert ang array ng IDs into string (e.g., "1,2,3")
            'display_album_ids' => $request->display_album_ids ? implode(',', $request->display_album_ids) : '', 
        ];

        foreach ($data as $key => $value) {
            DB::table('settings')->updateOrInsert(
                ['key' => $key],
                [
                    'value' => $value,
                    'updated_at' => Carbon::now() // Mahalaga para sa auto-refresh detection
                ]
            );
        }

        // I-save ang tab state para bumalik ang user sa Settings tab pagkatapos ng save
        session(['last_tab' => 'settings']);
        
        return back()->with('success', 'Settings updated successfully!');
    }
}