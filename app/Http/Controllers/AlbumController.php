<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Photo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class AlbumController extends Controller
{
    /**
     * Redirect to Albums after login is handled by RouteServiceProvider, 
     * but we ensure the index method is ready here.
     */

        public function reorder(Request $request) {
        $ids = $request->photo_ids;
        
        DB::transaction(function () use ($ids) {
            foreach ($ids as $index => $id) {
                Photo::where('id', $id)->update(['order' => $index]);
            }
        });

        return response()->json(['status' => 'success']);
    }

        public function getPhotos($id)
    {
        // 1. Find the album with its photos
        // Change 'photos' to whatever your relationship name is in the Album model
        $album = \App\Models\Album::with('photos')->find($id);

        if (!$album) {
            return response()->json(['error' => 'Album not found'], 404);
        }

        // 2. Return the photos as JSON
        // Make sure your Photo model has a 'path' attribute (or change 'path' in the JS)
        return response()->json($album->photos);
    }
    public function index()
    {
        $albums = Album::with(['photos' => function($query) {
            $query->latest(); 
        }])->latest()->get();

        

        return view('admin.albums.list', [
            'title' => 'Albums',
            'albums' => $albums,
        ]);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    public function recycle()
    {
        $trashedPhotosByAlbum = Photo::onlyTrashed()
            ->with(['album' => fn($q) => $q->withTrashed()])
            ->get()
            ->groupBy('album_id');

        return view('admin.recycle.list', [
            'title' => 'Recycle Bin',
            'trashedPhotosByAlbum' => $trashedPhotosByAlbum
        ]);
    }

    public function datatable()
    {
        $albums = Album::whereNull('deleted_at')->get(); 
        return response()->json(['data' => $albums]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|unique:albums,name',
            'description' => 'nullable'
        ]);

        Album::create($validated);
        return response()->json(['message' => 'Album created successfully!']);
    }

    public function show($id)
    {
        $album = Album::findOrFail($id);
        return response()->json($album);
    }

    public function update(Request $request, $id)
    {
        $album = Album::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|unique:albums,name,' . $album->id,
            'description' => 'nullable',
        ]);

        $album->update($validated);

        return response()->json(['message' => 'Album updated successfully!']);
    }

    public function destroy(Album $album)
    {
        Photo::where('album_id', $album->id)->delete();
        $album->delete();

        return back()->with('status', 'Album moved to Recycle Bin.')->with('last_tab', 'manage');
    }

       // Add this inside your PhotoController class
    public function restoreAlbum(Request $request)
    {
        $albumId = $request->input('album_id');

        // 1. I-restore ang mismong Album kung ito ay naka-soft delete din
        $album = Album::withTrashed()->find($albumId);
        if ($album && $album->trashed()) {
            $album->restore();
        }

        // 2. I-restore ang lahat ng photos sa ilalim ng album na ito
        // Ginagamit ang update(['is_active' => true]) para siguradong lilitaw sa gallery
        Photo::onlyTrashed()
            ->where('album_id', $albumId)
            ->each(function ($photo) {
                $photo->restore();
                $photo->update(['is_active' => true]);
            });

        return back()->with('success', 'Ang album at ang mga litrato nito ay naibalik na!');
    }

    public function forceDeleteAlbum($albumId)
    {
        $slides = Photo::onlyTrashed()->where('album_id', $albumId)->get();
        
        foreach ($slides as $slide) {
            if ($slide->image_path && Storage::disk('public')->exists($slide->image_path)) {
                Storage::disk('public')->delete($slide->image_path);
            }
            $slide->forceDelete();
        }

        $album = Album::withTrashed()->find($albumId);
        if ($album) {
            $album->forceDelete();
        }

        return back()->with('status', 'Album permanently deleted.')->with('last_tab', 'trash');
    }
}