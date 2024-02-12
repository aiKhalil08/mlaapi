<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
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

    // protected function modules(): Attribute
    // {
    //     return Attribute::make(
    //         get: fn ($value) => $value,
    //         set: function($value) {
    //             if (sizeof($value) == 1 && ($value[0]['overview'] == null || $value[0]['overview'] == null)) return null;
    //             else return json_encode($value);
    //         },
    //     );
    // }
    // protected function date(): Attribute
    // {
    //     return Attribute::make(
    //         get: fn ($value) => $value,
    //         set: function($value) {
    //             // var_dump($value); return null;
    //             if ($value['start'] == null) return null;
    //             else return json_encode($value);
    //         },
    //     );
    // }
}
