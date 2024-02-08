<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CertificateCourse;
use App\Models\CertificationCourse;
use App\Models\OffshoreCourse;

class CourseController extends Controller
{
    public function get(Request $request) {


        $certificate_courses = CertificateCourse::select(['code', 'title', 'price', 'discount'])->take(10)->orderBy('title', 'asc')->get();
        $certification_courses = CertificationCourse::select(['code', 'title', 'price', 'discount'])->take(10)->orderBy('title', 'asc')->get();
        $offshore_courses = OffshoreCourse::select(['title', 'location', 'price', 'discount'])->take(10)->orderBy('title', 'asc')->get();

        return response()->json(['certificate-courses'=>$certificate_courses, 'certification-courses'=>$certification_courses, 'offshore-courses'=>$offshore_courses], 200,);
    }
}
