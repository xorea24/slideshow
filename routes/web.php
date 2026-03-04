<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth; // Added missing Auth import
use App\Http\Controllers\PhotoController;
use App\Http\Controllers\AlbumController;
use App\Http\Controllers\SettingsController;

    Auth::routes();


Route::delete('/{album}', [AlbumController::class, 'destroy'])->name('albums.destroy');

// Public Routes
Route::get('/', [PhotoController::class, 'publicGallery'])->name('home');
Route::get('/gallery', [PhotoController::class, 'publicGallery'])->name('gallery.public');
Route::get('/dashboard', [AlbumController::class, 'dashboard'])->name('dashboard');

// Photos Group
Route::middleware(['auth'])->group(function () {
    // DASHBOARD
    Route::get('/dashboard', [PhotoController::class, 'index'])->name('dashboard');
    // PHOTO MANAGEMENT
    Route::post('/upload', [PhotoController::class, 'store'])->name('photos.store');
    // Siguraduhin na 'photos.update' ang name at PATCH ang method
    // Siguraduhin na PATCH ito at tumutugma ang 'photo.update'
    Route::patch('{photo}', [PhotoController::class, 'update'])->name('photos.update');
    Route::get('/photos/{photo}/toggle', [PhotoController::class, 'toggle'])->name('photos.toggle');
    Route::delete('/photos/{photo}', [PhotoController::class, 'destroy'])->name('photos.destroy');
        // 4. DESTROY / DELETE
    });

// Albums Group
Route::controller(AlbumController::class)
    ->prefix('albums')
    ->name('albums.') 
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/datatable', 'datatable')->name('datatable');
        Route::post('/', 'store')->name('store');  
        Route::get('/{id}', 'show')->name('show'); 
        Route::patch('/{id}', 'update')->name('update');
        
        // FIXED: Removed the extra 'albums.' prefix because it is already in the group name
        Route::delete('/{albumId}/force-delete', [AlbumController::class, 'forceDeleteAlbum'])->name('Photo.delete-album');
    });

// Positions Group
Route::controller(PositionController::class)
    ->prefix('positions')
    ->name('positions.')
    ->group(function () {
        Route::get('/', 'indexPage')->name('index'); 
        Route::get('/datatable', 'datatable')->name('datatable');
        Route::get('/search', 'search')->name('search');
        Route::post('/', 'store')->name('store');
        Route::get('/{id}', 'show')->name('show');
        Route::put('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'delete')->name('delete');
    });

// Applicants Group
Route::controller(PhotoController::class)
    ->prefix('recycle')
    ->name('recycle.')
    ->group(function () {
        Route::get('/', 'indexPage')->name('index');
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