<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Album;

class SettingsController extends Controller
{


    public function indexPage()
    {
        $albums = Album::with('photos')->orderBy('name')->get();
        
        // Fetch existing settings
        $settings = DB::table('settings')->pluck('value', 'key')->toArray();

        return view('admin.settings.list', [
            'title'    => 'System Configuration',
            'albums'   => $albums,
            'settings' => $settings,
        ]);
    }

    public function getLatestData() 
    {
        // FIX: Isama ang show_photo_info sa JSON response para sa monitoring
        $settings = DB::table('settings')->pluck('value', 'key');

        return response()->json([
            'seconds' => $settings['slide_duration'] ?? 5,
            'effect' => $settings['transition_effect'] ?? 'fade',
            'show_info' => $settings['show_photo_info'] ?? '1',
            // Kinukuha ang pinaka-latest na update time sa buong table
            'last_update' => DB::table('settings')->max('updated_at') ?? now()->toDateTimeString(),
        ]);
    }

   public function update(Request $request)
{
    // Validate the incoming data
    $data = $request->validate([
        'slide_duration'         => 'required|integer|min:1|max:60',
        'transition_effect'      => 'required|string',
        'show_photo_name'        => 'required|boolean',
        'show_photo_description' => 'required|boolean',
        'font_style'             => 'nullable|string',
        'font_color'             => 'nullable|string',
        'display_album_ids'      => 'nullable|string',
        'overlay_position'       => 'nullable|string',
    ]);

    // Loop through the validated data and update or create the setting in the DB
    foreach ($data as $key => $value) {
        \DB::table('settings')->updateOrInsert(
            ['key' => $key],
            [
                'value'      => $value,
                'updated_at' => now()
            ]
        );
    }

    return response()->json(['status' => 'success', 'message' => 'Settings Updated!']);
    }
}
