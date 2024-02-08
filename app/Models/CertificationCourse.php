<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Course;

class CertificationCourse extends Course
{
    // use HasFactory;
    // public $timestamps = false;
    // public $guarded = ['id'];
    // protected $hidden = ['id'];

    // public function getImageUrlAttribute(string $string) {
    //     // return 'http://localhost:8000/storage/'.$string;
    //     return request()->schemeAndHttpHost().'/storage/'.$string;
    //     // return route('storage/'.$string);
    // }
}
