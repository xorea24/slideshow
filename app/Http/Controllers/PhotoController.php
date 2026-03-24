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
        $this->middleware(['auth', 'is_admin'])->except(['publicGallery']);
    }
       /**
 * Display the slideshow for the public.
 */

    public function updateOrder(Request $request)
    {
       try {
            $order = $request->input('photo_ids') ?? $request->input('order'); 
            
            if (!$order || !is_array($order)) {
                return response()->json(['error' => 'No valid data provided'], 400);
            }

            // Loop through IDs sent from the frontend and update their position
            foreach ($order as $index => $id) {
                Photo::where('id', $id)->update([
                    'sort_order' => $index,
                    'updated_at' => now()
                ]);
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getPhotos($id) 
    {
        // Siguraduhin na existing ang album bago kuhanin ang photos
        $album = Album::findOrFail($id);

        // Kuhanin ang photos na naka-sort base sa 'sort_order' at 'created_at'
        $photos = $album->photos()
                        ->orderBy('sort_order', 'asc') // Added sorting by sort_order
                        ->orderBy('created_at', 'asc') // Fallback to created_at
                        ->get(['id', 'image_path', 'name', 'description']);

        return response()->json($photos);
    }

        public function forceDelete($id)
    {
        $photo = Photo::onlyTrashed()->findOrFail($id);
        
        // I-delete ang actual file sa storage bago i-delete ang record
        if (\Storage::disk('public')->exists($photo->image_path)) {
            \Storage::disk('public')->delete($photo->image_path);
        }
        
        $photo->forceDelete();

        return back()->with('success', 'Photo permanently deleted.');
    }

        public function restore($id)
    {
        // Hanapin ang photo sa mga "trashed" records
        $photo = Photo::onlyTrashed()->findOrFail($id);
        
        // I-restore ang photo
        $photo->restore();

        return back()->with('success', 'Photo restored successfully!');
    }

      public function indexPage()
    {
        // Eager load photos to prevent the "count() on null" error in Blade
         $albums = Album::with(['slides' => function($query) {
            $query->latest(); 
        }])->latest()->get();

        return view('admin.albums.list', [
            'title'  => 'Albums Management',
            'albums' => $albums,
        ]);
    }
      
    public function publicGallery()
    {
        // 1. FETCH SETTINGS (Use try-catch or ensure table exists first)
        $settings = \DB::table('settings')->get()->keyBy('key');
        $seconds = $settings->get('slide_duration')->value ?? 5;
        $effect = $settings->get('transition_effect')->value ?? 'fade';
        $displayAlbumIds = $settings->get('display_album_ids')->value ?? '';
        $albumIdArray = array_filter(explode(',', $displayAlbumIds));

        // 2. FETCH SLIDES
        $slidesQuery = \DB::table('photos')
            ->join('albums', 'photos.album_id', '=', 'albums.id')
            ->select('photos.*', 'albums.name as album_title', 'albums.description as album_desc')
            ->where('photos.is_active', 1);

        if (!empty($albumIdArray)) {
            $slidesQuery->whereIn('photos.album_id', $albumIdArray);
        }

        $slides = $slidesQuery->orderBy('photos.sort_order', 'asc')->orderBy('photos.created_at', 'desc')->get();

        // 3. MASTER TIMESTAMP (FIXED: lowercase updated_at)
        $lastUpdate = max(
            \DB::table('settings')->max('updated_at') ?? 0,
            \DB::table('photos')->max('updated_at') ?? 0,
            \DB::table('albums')->max('updated_at') ?? 0 // Fixed typo 'updAted'
        ) ?: now();

        // FIXED: Pointing to admin.slideshow based on your file structure
        return view('admin.slideshow', compact('slides', 'seconds', 'effect', 'lastUpdate'));
    }
    /**
     * Store multiple photos and/or a new album
     */
       public function store(Request $request)
    {
        // Validate arrays
        $request->validate([
            'images' => 'required|array',
            'images.*' => 'required|image|mimes:jpeg,png,jpg|max:2048',
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
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        // FIXED: Changed 'category_name' to 'name' to match your DB
        $photo->update([
            'name' => $request->name,
            'description' => $request->description, 
        ]);

        return back()
        ->with('status', 'Photo updated successfully!')
        ->with('last_tab', 'manage'); 
        
    }

    /**
     * SETTINGS: Update duration, effects, and active albums
     */


    public function toggle(Photo $photo)
    {
        $photo->is_active = !$photo->is_active;
        $photo->save();
        return back()->with('status', 'Visibility updated!');
    }

    public function toggleAll(Album $album)
    {
        $hasHidden = $album->slides()->where('is_active', false)->exists();
        $album->slides()->update(['is_active' => $hasHidden]);
        return back()->with('status', 'Album visibility updated successfully!');
    }

    public function clearAlbumPhotos(Album $album)
    {
        $photos = $album->photos;
        foreach ($photos as $photo) {
            $photo->delete();
        }
        return back()->with('success', 'All photos in the album have been deleted.');
    }

    public function destroy(Photo $photo)
    {
        // Simply delete the photo record and the file
        $photo->delete();

        // Check if the request came from your AJAX 'submitForm'
        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Photo removed successfully'
            ]);
        }

        return back()->with('success', 'Photo deleted.');
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'photo_ids' => 'required|array',
            'photo_ids.*' => 'integer|exists:photos,id'
        ]);

        Photo::whereIn('id', $request->photo_ids)->delete();

        return back()->with('success', count($request->photo_ids) . ' photos deleted.');
    }
}

