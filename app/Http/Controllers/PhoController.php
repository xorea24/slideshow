<?php

namespace App\Http\Controllers;

use App\Models\Photo;
use App\Models\Album;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PhotoController extends Controller
{

     public function index()
    {
        $albums = Album::orderBy('name')->get();

        // Reuse the existing album listing page
        return view('admin.recycle.list', [
            'title' => 'Albums',
            'albums' => $albums,
        ]);
    }

    public function publicGallery()
    {
        // Get only active photos (like active slides)
        $slides = Photo::with('album')
            ->where('is_active', true)
            ->orderBy('created_at', 'asc')
            ->get();

        // Simple static settings (you can later move these to DB like in uploading-slideshow)
        $seconds = 5;        // time per slide
        $effect  = 'fade';   // 'fade', 'slide-up', etc.

        return view('public.gallery', compact('slides', 'seconds', 'effect'));
    }

    /**
     * Store uploaded photos and assign them to an album.
     */
        public function store(Request $request)
    {
        // 1. Handle New Album Creation
        $albumId = $request->album_id;
        if ($request->album_id === 'new') {
            $album = Album::create([
                'name' => $request->new_album_name,
                'description' => $request->new_album_desc
            ]);
            $albumId = $album->id;
        }

        // 2. Loop through the uploaded rows
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $file) {
                $path = $file->store('photos', 'public');
                
                Photo::create([
                    'album_id' => $albumId ?: null,
                    'name' => $request->titles[$index] ?? $file->getClientOriginalName(),
                    'description' => $request->descriptions[$index] ?? null,
                    'image_path' => $path,
                ]);
            }
        }

        return redirect()->back()->with('status', 'Photos uploaded successfully!');
    }
}