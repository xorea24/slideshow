<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth; // Added missing Auth import
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ApplicantController;
use App\Http\Controllers\InterviewController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\PhotoController;
use App\Http\Controllers\AlbumController;

Auth::routes();

// Public Routes
Route::get('/', [PhotoController::class, 'publicGallery'])->name('home');
Route::get('/gallery', [PhotoController::class, 'publicGallery'])->name('gallery.public');
Route::get('/dashboard', [AlbumController::class, 'dashboard'])->name('dashboard');

// Photos Group
Route::controller(PhotoController::class)
    ->prefix('photos')
    ->name('photos.')
    ->group(function () {
        Route::post('/', 'store')->name('store');
        // Added these routes to fix your "Hide" and "Delete Photo" buttons
        Route::get('/{id}/toggle', 'toggleActive')->name('toggle'); 
        Route::delete('/{id}', 'destroy')->name('destroy');
    });

// Albums Group
Route::controller(AlbumController::class)
    ->prefix('albums')
    ->name('albums.') 
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/datatable', 'datatable')->name('datatable');
        Route::post('/', 'store')->name('store');
        Route::get('/recycle', 'recycle')->name('recycle');
        
        Route::get('/{id}', 'show')->name('show');
        Route::patch('/{id}', 'update')->name('update');
        
        // FIXED: Removed the extra 'albums.' prefix because it is already in the group name
        Route::delete('/{id}', 'destroy')->name('destroy'); 
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
Route::controller(ApplicantController::class)
    ->prefix('applicants')
    ->name('applicants.')
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
Route::controller(InterviewController::class)
    ->prefix('interviews')
    ->name('interviews.')
    ->group(function () {
        Route::get('/', 'indexPage')->name('index');
        Route::get('/datatable', 'datatable')->name('datatable');
        Route::get('/search', 'search')->name('search');
        Route::get('/{id}', 'show')->name('show');
        Route::post('/', 'store')->name('store');
        Route::put('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'delete')->name('delete');
    });