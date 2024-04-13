<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Testimonial extends Resource
{
    // use HasFactory;

    // public function getImageUrlAttribute(string | null $string) {
    //     if (!$string) return null;
    //     return Storage::url($string);
    //     // return request()->schemeAndHttpHost().'/storage/'.$string;
    // }
}
