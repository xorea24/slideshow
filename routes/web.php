<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth; // Added missing Auth import
use App\Http\Controllers\PhotoController;
use App\Http\Controllers\AlbumController;
use App\Http\Controllers\SettingsController;

// Auth routes 

Route::middleware(['auth'])->prefix('albums')->group(function () {
    Route::get('/', [AlbumController::class, 'index'])->name('albums.index');
    Route::delete('/{album}', [AlbumController::class, 'destroy'])->name('albums.destroy');
});
Auth::routes();

// Settings Update Route (AJAX)
Route::get('/settings/latest', [SettingsController::class, 'getLatestData']);

Route::post('/settings/update', [SettingsController::class, 'update'])->name('settings.update');
// Add this line to handle the photo updates (title and description)
// Siguraduhin na ang URL ay /photos/{id}/update
Route::patch('/settings', [SettingsController::class, 'update'])->name('settings.update');


// Siguraduhin na ang URL na ito ang tinatawag sa iyong JS fetch
Route::get('/api/get-latest-settings', function () {
    $lastSetting = DB::table('settings')->max('updated_at');
    $lastImage = DB::table('photos')->max('updated_at');
    $lastAlbum = DB::table('albums')->max('updated_at'); // Isama ang album updates

    return response()->json([
        'last_update' => max($lastSetting, $lastImage, $lastAlbum)
    ]);
});


/**
 * PUBLIC FACING VIEWS
 */
Route::get('/', function () {
    $displayAlbums = DB::table('settings')->where('key', 'display_album_ids')->value('value') ?? '';

    if ($displayAlbums === '' || $displayAlbums === null) {
        $slides = Photo::where('is_active', true)->orderBy('created_at', 'desc')->get();
    } else {
        $albumIds = array_map('intval', explode(',', $displayAlbums));
        $slides = Photo::where('is_active', true)->whereIn('album_id', $albumIds)->orderBy('created_at', 'desc')->get();
    }

    return view('public', compact('slides'));
});

Route::get('/public-Photo', function () {
    $displayAlbum = DB::table('settings')->where('key', 'display_album_id')->value('value') ?? 'all';
    $duration = DB::table('settings')->where('key', 'slide_duration')->value('value') ?? 5;
    $effect = DB::table('settings')->where('key', 'transition_effect')->value('value') ?? 'fade';

    if ($displayAlbum === 'all' || $displayAlbum === null) {
        $slides = Photo::where('is_active', true)->orderBy('created_at', 'desc')->get();
    } else {
        $slides = Photo::where('is_active', true)->where('album_id', $displayAlbum)->orderBy('created_at', 'desc')->get();
    }

    return view('public-Photo', compact('slides', 'duration', 'effect'));
});

// Public Slideshow
Route::get('/', [PhotoController::class, 'publicGallery'])->name('/');
Route::get('/publicGallery', [PhotoController::class, 'publicGallery'])->name('gallery.public');

// Change this line in web.php:
//Settings Section
Route::post('/settings/update', [SettingsController::class, 'update'])->name('settings.update');

// PhotoController::class, 'restoreAlbum' Section 
Route::get('/recycle', [AlbumController::class, 'recycle'])->name('recycle.index');
Route::patch('/{id}/restore', [PhotoController::class, 'restore'])->name('photos.restore');
Route::delete('/{albumId}/force-delete', [AlbumController::class, 'forceDeleteAlbum'])->name('Photo.delete-album');
Route::delete('/photos/{id}/force', [PhotoController::class, 'forceDelete'])->name('photos.forceDelete');

// Add these to fix the "Route not found" errors in your Blade files
Route::patch('/photos/restore-album', [AlbumController::class, 'restoreAlbum'])->name('photos.restore-album');
Route::delete('/photos/force-delete-album/{id}', [AlbumController::class, 'forceDeleteAlbum'])->name('Photo.delete-album');
Route::delete('/{album}', [AlbumController::class, 'destroy'])->name('albums.destroy');


// Change this line in web.php:


// Photos Group
Route::middleware(['auth'])->group(function () {
    Route::post('/upload', [PhotoController::class, 'store'])->name('photos.store');
    Route::patch('/photos/{photo}', [PhotoController::class, 'update'])->name('photos.update');
    Route::post('/photos/{photo}/toggle', [PhotoController::class, 'toggle'])->name('photos.toggle');
    Route::delete('/photos/{photo}', [PhotoController::class, 'destroy'])->name('photos.destroy');
    Route::post('/albums/{album}/toggle-all', [PhotoController::class, 'toggleAll'])->name('albums.toggleAll');
    });

// Albums Group
Route::controller(AlbumController::class)
    ->prefix('albums')
    ->name('albums.') 
    ->group(function () {
        // Ensure this points to the correct Controller and Method
        Route::get('/recycle', [AlbumController::class, 'recycle'])->name('recycle.index');
        Route::get('/albums', [AlbumController::class, 'index'])->name('admin.albums.list');
        Route::get('/datatable', 'datatable')->name('datatable');
        Route::post('/', 'store')->name('store');  
        Route::get('/{id}', 'show')->name('show'); 
        Route::patch('/{id}', 'update')->name('update');
        // FIXED: Removed the extra 'albums.' prefix because it is already in the group name
        Route::delete('/{albumId}/force-delete', [AlbumController::class, 'forceDeleteAlbum'])->name('Photo.delete-album');
    });



// Applicants Group
Route::controller(PhotoController::class)
    ->prefix('recycle')
    ->name('recycle.')
    ->group(function () {
        Route::get('recycle', 'index')->name('index');
        Route::get('/datatable', 'datatable')->name('datatable');
        Route::get('/search', 'search')->name('search');
        Route::get('/{id}', 'show')->name('show');
        Route::post('/', 'store')->name('store');
        Route::put('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'delete')->name('delete');
    });

// Interviews Group
Route::controller(SettingsController::class)
    ->prefix('settings')
    ->name('settings')
    ->group(function () {
        Route::get('/', 'indexPage')->name('index');
        Route::get('/datatable', 'datatable')->name('datatable');
        Route::get('/search', 'search')->name('search');
        Route::get('/{id}', 'show')->name('show');
        Route::post('/', 'store')->name('store');
        Route::put('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'delete')->name('delete');
    });

    //  home to albums
    Route::get('/home', function () {
    return redirect('/albums');
});

Route::post('/logout', [AlbumController::class, 'logout'])->name('logout');