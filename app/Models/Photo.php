<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // Import ito sa pinakataas

class Photo extends Model
{
    use SoftDeletes; // Gamitin ito sa loob ng class

    // Sa loob ng Photo.php o Slide.php
protected $fillable = ['name', 'description', 'image_path', 'album_id', 'is_active'];

    // FIX para sa RelationNotFoundException
    public function album()
    {
        return $this->belongsTo(Album::class);
    }
}