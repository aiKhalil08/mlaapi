<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\DB;

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

    public function carts(): MorphMany
    {
        return $this->morphMany(Cart::class, 'course');
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


    public static function trending_courses() {
        $courses = DB::select('select count(id) as number, course_type, course_id from carts group by course_type, course_id order by number desc limit 5');

        $trending_courses = [];

        if (count($courses) > 0) {
            foreach($courses as $course) {
                if ($course->course_type == 'App\Models\CertificateCourse') {
                    $trending_courses[] = ['name' => CertificateCourse::where('id', $course->course_id)->select(['title', 'code'])->first(), 'type'=>'Certificate Course'];
                } else if ($course->course_type == 'App\Models\CertificationCourse') {
                    $trending_courses[] = ['name' => CertificationCourse::where('id', $course->course_id)->select(['title', 'code'])->first(), 'type'=>'Certification Course'];
                } else if ($course->course_type == 'App\Models\OffshoreCourse') {
                    $trending_courses[] = ['name' => OffshoreCourse::where('id', $course->course_id)->select(['title'])->first(), 'type'=>'Offshore Course'];
                }
            }
        }

        return $trending_courses;
    }
}
