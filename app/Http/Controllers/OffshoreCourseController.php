<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OffshoreCourse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class OffshoreCourseController extends Controller
{
    public function store(Request $request) {

        try {
            DB::transaction(function () use ($request, &$course) {
                $attributes = ['title'=>$request->title, 'overview'=>$request->overview, 'objectives'=>json_encode($request->objectives), 'attendees'=>json_encode($request->attendees), 'prerequisites'=>json_encode($request->prerequisites), 'modules'=>json_encode($request->modules), 'date'=>json_encode($request->date), 'location'=>$request->location, 'price'=>json_encode($request->price), 'discount'=>$request->discount];

                if ($request->hasFile('image')) {
                    $name = strtolower(str_replace(' ', '_', $request->title));
                    $image_name = $name.'.'.$request->image->extension();
                    $image_url = $request->image->storeAs('images/offshore_courses', $image_name);
                    $attributes = [...$attributes, 'image_url'=>$image_url];
                }
                // if ($request->hasFile('schedule')) {
                //     $name = strtolower(str_replace(' ', '_', $request->title));
                //     $schedule_name = $name.'.'.$request->schedule->extension();
                //     $schedule_url = $request->schedule->storeAs('schedule/offshore_courses', $schedule_name);
                // } else $schedule_url = '';
                $course = OffshoreCourse::create($attributes);
            });
            return response()->json(['status'=>'success'], 200);
            // return $course;
        } catch (\Throwable $th) {
            return response()->json(['status'=>'failed', 'message'=>'Something went wrong. Please try again.'], 200);
        }
    }


    public function edit(Request $request, string $course_title) {

        try {
            $course = OffshoreCourse::where('title', $course_title)->first();
            DB::transaction(function () use ($request, &$course) {
                $name = strtolower(str_replace(' ', '_', $request->title));
                $attributes = ['title'=>$request->title, 'overview'=>$request->overview, 'objectives'=>json_encode($request->objectives), 'attendees'=>json_encode($request->attendees), 'prerequisites'=>json_encode($request->prerequisites), 'modules'=>json_encode($request->modules), 'date'=>json_encode($request->date), 'location'=>$request->location, 'price'=>json_encode($request->price), 'discount'=>$request->discount,];

                if ($request->hasFile('image')) {
                    $image_name = $name.'.'.$request->image->extension();
                    $image_url = $request->image->storeAs('images/offshore_courses', $image_name);
                    // substr($image_url, 7)
                    $attributes = [...$attributes, 'image_url'=>$image_url];
                }
                // if ($request->hasFile('schedule')) {
                //     // $schedule_name = $name.'.'.$request->schedule->extension();
                //     $schedule_url = '';#$request->schedule->storeAs('schedule/offshore_courses', $schedule_name);
                //     $attributes = [...$attributes, 'schedule_url'=>substr($schedule_url, 7)];
                // }
                $course->update($attributes);
            });
            return response()->json(['status'=>'success'], 200);
            // return $course;
        } catch (\Throwable $th) {
            return response()->json(['status'=>'failed', 'message'=>'Something went wrong. Please try again.'], 200);
        }
    }

    public function delete(Request $request, string $course_title) {

        try { 
            
            $course = OffshoreCourse::where('title', $course_title)->first();
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
            $courses = OffshoreCourse::select(['title', 'location', DB::raw('SUBSTRING(overview, 1, 100) as sub_overview')])->take($count)->orderBy('title', 'asc')->get();
        } else {
            $courses = OffshoreCourse::select(['title', 'location', DB::raw('SUBSTRING(overview, 1, 100) as sub_overview')])->orderBy('title', 'asc')->get();
        }
        return response()->json($courses, 200);
    }

    public function get(Request $request, string $course_title) {

        $course = OffshoreCourse::where('title', $course_title)->first();

        if (!$course) return response()->json(['status'=>'failed', 'message'=>'No course with such name.'], 200);

        return response()->json(['status'=>'success', 'course'=>$course], 200);
    }


    public function get_names() {
        return OffshoreCourse::select(['title'])->get();
    }
}
