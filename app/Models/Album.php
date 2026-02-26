<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Album extends Model
{
    use SoftDeletes; // Napakahalaga para sa Recycle functionality

    protected $fillable = ['name', 'description'];

    public function photos()
    {
        return $this->hasMany(Photo::class);
    }
}