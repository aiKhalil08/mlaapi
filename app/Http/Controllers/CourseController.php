<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CertificateCourse;
use App\Models\CertificationCourse;
use App\Models\OffshoreCourse;
use App\Models\Course;
use App\Models\User;
use App\Models\Student;
use Illuminate\Support\Facades\DB;

class CourseController extends Controller
{
    public function get(Request $request) {


        $certificate_courses = CertificateCourse::select(['code', 'title', 'price', 'discount'])->orderBy('title', 'asc')->get();
        $certification_courses = CertificationCourse::select(['code', 'title'])->orderBy('title', 'asc')->get();
        $offshore_courses = OffshoreCourse::select(['title', 'location', 'price', 'discount'])->orderBy('title', 'asc')->get();

        return response()->json(['certificate-courses'=>$certificate_courses, 'certification-courses'=>$certification_courses, 'offshore-courses'=>$offshore_courses], 200,);
    }


    public function trendingCourses() {

        return response()->json(['courses' => Course::trending_courses()], 200);
    }

    public function get_enrolled_students(string $type, string $course_identity) {
        
        $course = match ($type) {
            'certificate_course' => CertificateCourse::where('code', $course_identity)->first(),
            'certification_course' => CertificationCourse::where('code', $course_identity)->first(),
            'offshore_course' => OffshoreCourse::where('title', $course_identity)->first(),
        };
        
        if (!$course) return response()->json(['status'=> 'failed', 'message'=>'No course with such name'], 200);
       

        $students = User::areStudents()->select('users.id', 'first_name', 'last_name', 'email')
        ->join('sales', 'users.id', '=', 'sales.user_id') // gets all students that have made a purchase
        ->where('sales.sale_type_id', '2') // filters purchases to only include individual course purchases (type_id = 2)
        ->where('sales.course_type', $course::class) //filters purchases to only include specified course category
        ->where('sales.course_id', $course->id) //filters purchases to only include specified course id
        ->get()->map(function ($user) {
            return new Student($user->makeVisible('id')->toArray());
        });

        $students->load(['certificate' => function ($query) use ($course) {
            $query->select(['url', 'user_id'])
                ->where('course_type', $course::class)
                ->where('course_id', $course->id)
                ->limit(1);
        }]);

        return response()->json(['status'=>'success','students'=>$students], 200);
    }
}
