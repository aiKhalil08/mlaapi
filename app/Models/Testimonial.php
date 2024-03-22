<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Testimonial extends Resource
{
    // use HasFactory;

    public function getImageUrlAttribute(string | null $string) {
        if (!$string) return null;
        return request()->schemeAndHttpHost().'/storage/'.$string;
    }
}
