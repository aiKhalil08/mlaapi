<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Resource extends Model
{
    use HasFactory;

    public $timestamps = false;
    public $guarded = ['id'];
    protected $hidden = ['id'];

    public function getImagePathAttribute() {
        return explode('storage/', $this->image_url)[1];
    }

    public function getImageUrlAttribute(string | null $string) {
        if (!$string) return null;
        return Storage::url($string);
    }
}
