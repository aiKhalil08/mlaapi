<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Student;
use App\Models\CertificateCourse;
use App\Models\CertificationCourse;
use App\Models\OffshoreCourse;

class CartController extends Controller
{
    public function add(Request $request) {
        $type = $request->type;
        if ($type == 'certificate-course') {
            $course = CertificateCourse::where('code', $request->course_code)->first();
        } else if ($type == 'certification-course') {
            $course = CertificationCourse::where('code', $request->course_code)->first();
        } else if ($type == 'offshore-course') {
            $course = OffshoreCourse::where('title', $request->course_title)->first();
        }

        $student = auth()->user();

        if (!Cart::add($student, $course)) return response()->json(['status'=>'failed', 'message'=>'Could not cart course'], 200);

        return response()->json(['status'=>'success', 'message'=>'Course carted', 'cart'=>base64_encode(json_encode($student->carted_courses_titles()))], 200);
    }
}
