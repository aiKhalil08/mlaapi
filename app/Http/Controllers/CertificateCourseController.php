<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CertificateCourse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class CertificateCourseController extends Controller
{
    public function store(Request $request) {
        try {
            $course = null;
            DB::transaction(function () use ($request, &$course) {
                $attributes = ['code'=>$request->code, 'title'=>$request->title, 'overview'=>$request->overview, 'objectives'=>$request->objectives, 'attendees'=>$request->attendees, 'prerequisites'=>$request->prerequisites, 'modules'=>$request->modules, 'price'=>$request->price, 'discount'=>$request->discount];

                if ($request->hasFile('image')) {
                    $name = strtolower(str_replace(' ', '_', $request->code));
                    $image_name = $name.'.'.$request->image->extension();
                    $image_url = $request->image->storeAs('/images/certificate_courses', $image_name);
                    $attributes = [...$attributes, 'image_url'=>$image_url];
                }
                
                $course = CertificateCourse::create($attributes);
            });
            return response()->json(['status'=>'success'], 200);
        } catch (\Throwable $th) {
            return response()->json(['status'=>'failed', 'message'=>'Something went wrong. Please try again.'], 200);
        }
    }


    public function edit(Request $request, string $course_code) {
        try {
            $course = CertificateCourse::where('code', $course_code)->first();
            DB::transaction(function () use ($request, &$course) {
                $name = strtolower(str_replace(' ', '_', $request->code));
                $attributes = ['code'=>$request->code, 'title'=>$request->title, 'overview'=>$request->overview, 'objectives'=>$request->objectives, 'attendees'=>$request->attendees, 'prerequisites'=>$request->prerequisites, 'modules'=>$request->modules, 'price'=>$request->price, 'discount'=>$request->discount];
    
                if ($request->hasFile('image')) {
                    $image_name = $name.'.'.$request->image->extension();
                    $image_url = $request->image->storeAs('images/certificate_courses', $image_name);
                    $attributes = [...$attributes, 'image_url'=>$image_url];
                }
                
                $course->update($attributes);
            });
            return response()->json(['status'=>'success'], 200);
        } catch (\Throwable $th) {
            return response()->json(['status'=>'failed', 'message'=>'Something went wrong. Please try again.'], 200);
        }
    }

    public function delete(Request $request, string $course_code) {
        try {
            
            $course = CertificateCourse::where('code', $course_code)->first();
            DB::transaction(function () use ($request, &$course) {
                if ($course->image_url) $image_path = $course->image_path;
                $course->delete();    
                if ($course->image_url) Storage::delete([$image_path, /* $schedule_path */]);
            });
            return response()->json(['status'=>'success'], 200,);
        } catch (\Throwable $th) {
            return response()->json(['status'=>'failed', 'message'=>'Something went wrong. Please try again.'], 200);
        }
    }

    public function get_list(Request $request, string $count) {
        if ($count != 'all') {
            $courses = CertificateCourse::select(['code', 'title', DB::raw('SUBSTRING(overview, 1, 100) as sub_overview')])->take($count)->orderBy('title', 'asc')->get();
        } else {
            $courses = CertificateCourse::select(['code', 'title', DB::raw('SUBSTRING(overview, 1, 100) as sub_overview')])->orderBy('title', 'asc')->get();
        }
        return response()->json($courses, 200);
    }

    public function get(Request $request, string $course_code) {
        $course = CertificateCourse::where('code', $course_code)->first();

        if (!$course) return response()->json(['status'=>'failed', 'message'=>'No course with such name.'], 200);

        return response()->json(['status'=>'success', 'course'=>$course], 200);
    }

    public function get_names() {
        return CertificateCourse::select(['code', 'title'])->get();
    }
}
