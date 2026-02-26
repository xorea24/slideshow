<?php
namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Photo;
use Illuminate\Http\Request;

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

        // Reuse the existing album listing page
        return view('admin.albums.list', [
            'title' => 'Albums',
            'albums' => $albums,
        ]);
    }

    public function datatable()
    {
        // Siguraduhin na ang SBR helper mo ay naka-setup para dito
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
}