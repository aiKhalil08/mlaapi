<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CertificateCourse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class CertificateCourseController extends Controller
{
    public function store(Request $request) {
        // echo $request->file('image')->isValid();
        // var_dump($request->all());
        $image_name = strtolower(str_replace(' ', '_', $request->code));
        $image_name = $image_name.'.'.$request->image->extension();
        // var_dump(json_encode($request->modules));
        // Storage::put("$image_name.png", base64_decode($request->image));
        $image_url = $request->image->storeAs('/public/images/courses', $image_name);
        $course = CertificateCourse::create(['code'=>$request->code, 'title'=>$request->title, 'overview'=>$request->overview, 'objectives'=>json_encode($request->objectives), 'attendees'=>json_encode($request->attendees), 'prerequisites'=>json_encode($request->prerequisites), 'modules'=>json_encode($request->modules), 'date'=>json_encode($request->date), 'price'=>json_encode($request->price), 'discount'=>$request->discount, 'image_url'=>$image_url]);
        // return response()->json(request()->all(), 200,);
        return $course;
    }

    public function get_list(Request $request, string $count) {
        if ($count != 'all') {
            $courses = CertificateCourse::select(['code', 'title', DB::raw('SUBSTRING(overview, 1, 100) as sub_overview')])->take($count)->orderBy('title', 'asc')->get();
        } else {
            $courses = CertificateCourse::select(['code', 'title', DB::raw('SUBSTRING(overview, 1, 100) as sub_overview')])->orderBy('title', 'asc')->all();
        }
        return response()->json($courses, 200);
    }

    public function get(Request $request, string $course_code) {
        // if ($count != 'all') {
        //     $courses = CertificateCourse::select(['code', 'title', DB::raw('SUBSTRING(overview, 1, 100) as sub_overview')])->take($count)->orderBy('title', 'asc')->get();
        // } else {
        //     $courses = CertificateCourse::select(['code', 'title', DB::raw('SUBSTRING(overview, 1, 100) as sub_overview')])->orderBy('title', 'asc')->all();
        // }
        $course = CertificateCourse::where('code', $course_code)->first();
        return response()->json($course, 200);
    }
}
