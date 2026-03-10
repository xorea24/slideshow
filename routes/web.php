<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\PhotoController;
use App\Http\Controllers\AlbumController;
use App\Http\Controllers\SettingsController;
use App\Models\Photo;

/**
 * 1. LOGIN REDIRECT & AUTH ROUTES
 */
Route::get('/', function () {
    return redirect()->route('login');
});

Auth::routes();

/**
 * 2. PUBLIC FACING VIEWS (Slideshow)
 */
Route::get('/public-gallery', [PhotoController::class, 'publicGallery'])->name('gallery.public');

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

// API for real-time updates
Route::get('/api/get-latest-settings', function () {
    $lastSetting = DB::table('settings')->max('updated_at');
    $lastImage = DB::table('photos')->max('updated_at');
    $lastAlbum = DB::table('albums')->max('updated_at');

    return response()->json([
        'last_update' => max($lastSetting, $lastImage, $lastAlbum)
    ]);
});

/**
 * 3. AUTHENTICATED ROUTES (Admin Section)
 */
Route::middleware(['auth'])->group(function () {

    // Home redirect to albums
    Route::get('/home', function () {
        return redirect()->route('albums.index');
    });

    // Albums
    Route::prefix('albums')->name('albums.')->group(function () {
        Route::get('/', [AlbumController::class, 'index'])->name('index');
        Route::post('/', [AlbumController::class, 'store'])->name('store');
        Route::get('/{id}', [AlbumController::class, 'show'])->name('show');
        Route::patch('/{id}', [AlbumController::class, 'update'])->name('update');
        Route::delete('/{album}', [AlbumController::class, 'destroy'])->name('destroy');
        Route::post('/{album}/toggle-all', [PhotoController::class, 'toggleAll'])->name('toggleAll');
    });

    // Photos
    Route::prefix('photos')->name('photos.')->group(function () {
        Route::post('/upload', [PhotoController::class, 'store'])->name('store');
        Route::patch('/{photo}', [PhotoController::class, 'update'])->name('update');
        Route::post('/{photo}/toggle', [PhotoController::class, 'toggle'])->name('toggle');
        Route::delete('/{photo}', [PhotoController::class, 'destroy'])->name('destroy');
        Route::patch('/restore-album', [AlbumController::class, 'restoreAlbum'])->name('restore-album');
        Route::delete('/{id}/force', [PhotoController::class, 'forceDelete'])->name('forceDelete');
    });

    // Recycle Bin
    Route::get('/recycle', [AlbumController::class, 'recycle'])->name('recycle.index');
    Route::patch('/photos/{id}/restore', [PhotoController::class, 'restore'])->name('photos.restore');
    Route::delete('/albums/{albumId}/force-delete', [AlbumController::class, 'forceDeleteAlbum'])->name('Photo.delete-album');

    // Settings (FIXED: One route name only)
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'indexPage'])->name('index');
        Route::get('/latest', [SettingsController::class, 'getLatestData']);
        Route::post('/update', [SettingsController::class, 'update'])->name('update');
    });

    Route::post('/logout', [AlbumController::class, 'logout'])->name('logout');
});