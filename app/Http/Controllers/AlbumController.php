<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Photo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage; // Added missing import for force deletion

class AlbumController extends Controller
{
    public function dashboard()
    {
        $albums = Album::with(['photos' => function ($query) {
            $query->whereNull('deleted_at');
        }])->orderBy('name')->get();

        return view('admin.dashboard', [
            'title' => 'Albums Management',
            'albums' => $albums,
        ]);
    }

    public function index()
    {
        $albums = Album::orderBy('name')->get();

        return view('admin.albums.list', [
            'title' => 'Albums',
            'albums' => $albums,

            
        ]);

        return view('admin.recycle.list', [
            'title' => 'recycle',
            'recycledAlbums' => $recycledAlbums,

            
        ]);
    }

    /**
     * This method handles the /recycle route.
     * Use this instead of the missing ApplicantController.
     */
    public function recycle()
    {
        $trashedAlbums = Album::onlyTrashed()->get();
        $trashedPhotos = Photo::onlyTrashed()->with('album')->get();

        return view('admin.recycle', [
            'title' => 'Recycle Bin',
            'trashedAlbums' => $trashedAlbums,
            'trashedPhotos' => $trashedPhotos,
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
        // Soft delete all slides in this album
        Photo::where('album_id', $album->id)->delete();

        // Soft delete the album itself
        $album->delete();

        return back()->with('status', 'Album moved to Recycle Bin.')->with('last_tab', 'manage');
    }

    public function restoreAlbum(Request $request)
    {
        $albumId = $request->input('album_id');
        $album = Album::withTrashed()->find($albumId);

        if ($album) {
            $album->restore();
        }

        Photo::onlyTrashed()
            ->where('album_id', $albumId)
            ->restore();

        return back()->with([
            'status' => 'Album content restored.',
            'last_tab' => 'trash'
        ]);
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