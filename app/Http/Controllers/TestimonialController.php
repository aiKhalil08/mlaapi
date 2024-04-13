<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Testimonial;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class TestimonialController extends Controller
{
    public function store(Request $request) {

        try {
            $testimonial = null;
            DB::transaction(function () use ($request, &$testimonial) {
                $attributes = ['name'=>$request->name, 'message'=>preg_replace('/\s+/', ' ', $request->message), 'company'=>$request->company, 'designation'=>$request->designation];
                if ($request->hasFile('image')) {
                    $name = strtolower(str_replace(' ', '_', $request->name));
                    $image_name = $name.'.'.$request->image->extension();
                    $image_url = $request->image->storeAs('images/testimonials', $image_name);
                    $attributes = [...$attributes, 'image_url'=>$image_url,];
                }
                $testimonial = Testimonial::create($attributes);
            });
            return response()->json(['status'=>'success'], 200);
        } catch (\Throwable $th) {
            return response()->json(['status'=>'failed', 'message'=>'Something went wrong. Please try again.'], 200);
        }

    }


    public function edit(Request $request, string $name) {

        try {
            $testimonial = Testimonial::where('name', $name)->first();
            DB::transaction(function () use ($request, &$testimonial) {
                $name = strtolower(str_replace(' ', '_', $request->name));
                $attributes = ['name'=>$request->name, 'message'=>preg_replace('/\s+/', ' ', $request->message), 'company'=>$request->company, 'designation'=>$request->designation];

                if ($request->hasFile('image')) {
                    $image_name = $name.'.'.$request->image->extension();
                    $image_url = $request->image->storeAs('images/testimonials', $image_name);
                    $attributes = [...$attributes, 'image_url'=>$image_url];
                }
                $testimonial->update($attributes);
            });
            return response()->json(['status'=>'success'], 200);
        } catch (\Throwable $th) {
            return response()->json(['status'=>'failed', 'message'=>'Something went wrong. Please try again.'], 200);
        }

    }

    public function delete(Request $request, string $name) {
        try {
            $testimonial = Testimonial::where('name', $name)->first();
            DB::transaction(function () use ($request, &$testimonial) {
                if ($testimonial->image_url) $image_path = $testimonial->image_path;
                $testimonial->delete();
                if ($testimonial->image_url) Storage::delete([$image_path]);
            });
            return response()->json(['status'=>'success'], 200,);
        } catch (\Throwable $th) {
            return response()->json(['status'=>'failed', 'message'=>'Something went wrong. Please try again.'], 200);
        }
    }

    public function get_list(Request $request, string $count) {
        if ($count != 'all') {
            $testimonials = Testimonial::select(['name', 'company', 'designation', 'message', 'image_url'])->take($count)->orderBy('id', 'desc')->get();
        } else {
            $testimonials = Testimonial::select(['name', 'company', 'designation', 'message', 'image_url'])->orderBy('id', 'desc')->get();
        }
        return response()->json($testimonials, 200);
    }

    public function get(Request $request, string $name) {

        $testimonial = Testimonial::where('name', $name)->first();

        if (!$testimonial) return response()->json(['status'=>'failed', 'message'=>'No testimonial with such name.'], 200);

        return response()->json(['status'=>'success', 'testimonial'=>$testimonial], 200);
    }
}
