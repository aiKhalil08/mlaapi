<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CertificateCourse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class CertificateCourseController extends Controller
{
    public function store(Request $request) {
        $course = null;
        DB::transaction(function () use ($request, &$course) {
            if ($request->hasFile('image')) {
                $name = strtolower(str_replace(' ', '_', $request->code));
                $image_name = $name.'.'.$request->image->extension();
                $image_url = $request->image->storeAs('/public/images/certificate_courses', $image_name);
            } else $image_url = '';
            if ($request->hasFile('schedule')) {
                $name = strtolower(str_replace(' ', '_', $request->code));
                $schedule_name = $name.'.'.$request->schedule->extension();
                $schedule_url = $request->schedule->storeAs('/public/schedule/certificate_courses', $schedule_name);
            } else $schedule_url = '';
            $course = CertificateCourse::create(['code'=>$request->code, 'title'=>$request->title, 'overview'=>$request->overview, 'objectives'=>json_encode($request->objectives), 'attendees'=>json_encode($request->attendees), 'prerequisites'=>json_encode($request->prerequisites), 'modules'=>json_encode($request->modules), 'date'=>json_encode($request->date), 'price'=>json_encode($request->price), 'discount'=>$request->discount, 'image_url'=>substr($image_url, 7), 'schedule_url'=>substr($schedule_url, 7)]);
        });
        return $course;
    }


    public function edit(Request $request, string $course_code) {
        $course = CertificateCourse::where('code', $course_code)->first();
        DB::transaction(function () use ($request, &$course) {
            $name = strtolower(str_replace(' ', '_', $request->code));
            $attributes = ['code'=>$request->code, 'title'=>$request->title, 'overview'=>$request->overview, 'objectives'=>json_encode($request->objectives), 'attendees'=>json_encode($request->attendees), 'prerequisites'=>json_encode($request->prerequisites), 'modules'=>json_encode($request->modules), 'date'=>json_encode($request->date), 'price'=>json_encode($request->price), 'discount'=>$request->discount,];

            if ($request->hasFile('image')) {
                $image_name = $name.'.'.$request->image->extension();
                $image_url = $request->image->storeAs('/public/images/certificate_courses', $image_name);
                // substr($image_url, 7)
                $attributes = [...$attributes, 'image_url'=>substr($image_url, 7)];
            }
            if ($request->hasFile('schedule')) {
                // $schedule_name = $name.'.'.$request->schedule->extension();
                $schedule_url = '';#$request->schedule->storeAs('/public/schedule/certificate_courses', $schedule_name);
                $attributes = [...$attributes, 'schedule_url'=>substr($schedule_url, 7)];
            }
            // var_dump($attributes);return null;
            $course->update($attributes);
        });
        return $course;
    }

    public function delete(Request $request, string $course_code) {
        $course = CertificateCourse::where('code', $course_code)->first();
        DB::transaction(function () use ($request, &$course) {
            $image_path = '/public/'.$course->actual_image_url;
            // $schedule_path = '/public/'.$course->actual_schedule_url;
            // var_dump($image_path);
            // return null;
            $course->delete();
            Storage::delete([$image_path, /* $schedule_path */]);
        });
        return response()->json(['status'=>'success'], 200,);
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
        return response()->json($course, 200);
    }
}
