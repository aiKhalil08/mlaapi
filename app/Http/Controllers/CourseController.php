<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CertificateCourse;
use App\Models\CertificationCourse;
use App\Models\OffshoreCourse;
use App\Models\Course;

class CourseController extends Controller
{
    public function get(Request $request) {


        $certificate_courses = CertificateCourse::select(['code', 'title', 'price', 'discount'])->orderBy('title', 'asc')->get();
        $certification_courses = CertificationCourse::select(['code', 'title', 'price', 'discount'])->orderBy('title', 'asc')->get();
        $offshore_courses = OffshoreCourse::select(['title', 'location', 'price', 'discount'])->orderBy('title', 'asc')->get();

        return response()->json(['certificate-courses'=>$certificate_courses, 'certification-courses'=>$certification_courses, 'offshore-courses'=>$offshore_courses], 200,);
    }


    public function trending_courses() {

        return response()->json(['courses' => Course::trending_courses()], 200);
        // var_dump(Course::trending_courses()); return null;
    }
}
