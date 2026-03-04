<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Photo;
use Illuminate\Http\Request;
use App\Services\PositionService;
use App\Http\Traits\ApiResponses;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\Select2Resource;
use Illuminate\Support\Facades\Validator;

class PhotoController extends Controller
{
    use ApiResponses;

    public function __construct(
        private PositionService $positionService
    ) {
        $this->middleware(['auth', 'is_admin']);
    }

    /**
     * Display the main gallery management page
     */
    
    public function indexPage()
    {
        // Eager load photos to prevent the "count() on null" error in Blade
        $albums = Album::with('photos')->orderBy('name')->get();

        return view('admin.albums.list', [
            'title'  => 'Albums Management',
            'albums' => $albums,
        ]);
    }

    /**
     * Store multiple photos and/or a new album
     */
       public function store(Request $request)
    {
        // Validate arrays
        $request->validate([
            'images' => 'required|array',
            'name' => 'required|array', // Matches your name[] input
            'descriptions' => 'nullable|array',
            'album_id' => 'nullable'
        ]);

        $albumId = $request->album_id;

        // Create new album if requested
        if ($request->album_id === 'new') {
            $album = \App\Models\Album::create([
                'name' => $request->new_album_name,
                'description' => $request->new_album_desc,
            ]);
            $albumId = $album->id;
        }

        // IMPORTANT: Loop through the index to match each image with its title
        foreach ($request->file('images') as $index => $image) {
        // This returns 'photos/filename.jpg' which is what you save to the DB
        $path = $image->store('photos', 'public');

            Photo::create([
                'album_id'    => $albumId,
                'image_path'  => $path,
                'name'        => $request->name[$index], // Fixes "Untitled" issue
                'description' => $request->descriptions[$index] ?? null, // Saves description
            ]);
        }

        return response()->json(['message' => 'Success']);
    }


    /**
     * Update photo details
     */
    public function update(Request $request, Photo $photo)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $photo->update([
            'title' => $request->title,
            'description' => $request->description,
        ]);

        return back()->with('status', 'Photo updated successfully!')->with('last_tab', 'manage');
    }

    /**
     * Toggle photo visibility
     */
    public function toggle(Photo $photo)
    {
        $photo->is_active = !$photo->is_active;
        $photo->save();
        return back()->with('status', 'Visibility updated!');
    }

    /**
     * Toggle all photos in an album
     */
    public function toggleAll(Album $album)
    {
        // Standardized to use 'photos()' relationship
        $hasHidden = $album->photos()->where('is_active', false)->exists();
        $album->photos()->update(['is_active' => $hasHidden]);
        return back()->with('status', 'Album visibility updated successfully!');
    }

    /**
     * Remove photo and delete album if empty
     */
   public function destroy(Photo $photo)
{
    // Delete physical file
    if (\Storage::disk('public')->exists($photo->image_path)) {
        \Storage::disk('public')->delete($photo->image_path);
    }
    
    $photo->delete();
    return response()->json(['success' => true]);
    }
}


