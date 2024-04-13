<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CertificateCourse;
use App\Models\CertificationCourse;
use App\Models\OffshoreCourse;
use App\Models\Course;
use Illuminate\Support\Facades\DB;

class CourseController extends Controller
{
    public function get(Request $request) {


        $certificate_courses = CertificateCourse::select(['code', 'title', 'price', 'discount'])->orderBy('title', 'asc')->get();
        $certification_courses = CertificationCourse::select(['code', 'title'])->orderBy('title', 'asc')->get();
        $offshore_courses = OffshoreCourse::select(['title', 'location', 'price', 'discount'])->orderBy('title', 'asc')->get();

        return response()->json(['certificate-courses'=>$certificate_courses, 'certification-courses'=>$certification_courses, 'offshore-courses'=>$offshore_courses], 200,);
    }


    public function trending_courses() {

        return response()->json(['courses' => Course::trending_courses()], 200);
        // var_dump(Course::trending_courses()); return null;
    }

    public function get_enrolled_students(string $type, string $course_identity) {
        
        $course = match ($type) {
            'certificate_course' => CertificateCourse::where('code', $course_identity)->first(),
            'certification_course' => CertificationCourse::where('code', $course_identity)->first(),
            'offshore_course' => OffshoreCourse::where('title', $course_identity)->first(),
        };
        
        if (!$course) return response()->json(['status'=> 'failed', 'message'=>'No course with such name'], 200);
       
        $course_type = get_class($course);



        $students = DB::select('select distinct s.first_name, s.last_name, s.email, (select url from certificates where certificates.student_id = s.id and certificates.course_type = ? and certificates.course_id = ?) as certificate from students as s inner join sales on s.id = sales.student_id and sale_type_id = 2 and sales.course_type = ? and sales.course_id = ?', [$course_type, $course->id, $course_type, $course->id]);

        return response()->json(['status'=>'success','students'=>$students], 200);
    }
}
