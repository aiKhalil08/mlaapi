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
        $category = $request->category;
        if ($category == 'certificate-course') {
            $course = CertificateCourse::where('code', $request->course_code)->first();
        } else if ($category == 'certification-course') {
            $course = CertificationCourse::where('code', $request->course_code)->first();
        } else if ($category == 'offshore-course') {
            $course = OffshoreCourse::where('title', $request->course_title)->first();
        }

        $student = auth()->user();

        if (!Cart::add($student, $course)) return response()->json(['status'=>'failed', 'message'=>'Could not cart course'], 200);

        return response()->json(['status'=>'success', 'message'=>'Course carted', 'cart'=>base64_encode(json_encode($student->carted_courses_titles()))], 200);
    }

    public function remove(Request $request) {
        $category = $request->category;
        if ($category == 'Certificate course') {
            $course = CertificateCourse::where('code', $request->course_code)->first();
        } else if ($category == 'Certification course') {
            $course = CertificationCourse::where('code', $request->course_code)->first();
        } else if ($category == 'Offshore course') {
            $course = OffshoreCourse::where('title', $request->course_title)->first();
        }

        $student = auth()->user();


        if (!$student->carts()->where(['course_type'=>get_class($course), 'course_id'=>$course->id])->delete()) return response()->json(['status'=>'failed', 'message'=>'Something went wrong.'], 200);

        return response()->json(['status'=>'success', 'message'=>'Course removed', 'cart'=>base64_encode(json_encode($student->carted_courses_titles()))], 200);
    }
}
