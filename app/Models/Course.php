<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    public $timestamps = false;
    public $guarded = ['id'];
    protected $hidden = ['id'];

    public function getActualImageUrlAttribute() {
        return explode('storage/', $this->image_url)[1];
    }
    public function getActualScheduleUrlAttribute() {
        return explode('storage/', $this->schedule_url)[1];
    }

    public function getImageUrlAttribute(string $string) {
        // return 'http://localhost:8000/storage/'.$string;
        return request()->schemeAndHttpHost().'/storage/'.$string;
        // return route('storage/'.$string);
    }

    public function getScheduleUrlAttribute(string $string) {
        // return 'http://localhost:8000/storage/'.$string;
        return request()->schemeAndHttpHost().'/storage/'.$string;
        // return route('storage/'.$string);
    }
}
