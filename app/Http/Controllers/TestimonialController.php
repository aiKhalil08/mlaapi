<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Testimonial;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class TestimonialController extends Controller
{
    public function store(Request $request) {
        $testimonial = null;
        DB::transaction(function () use ($request, &$testimonial) {
            if ($request->hasFile('image')) {
                $name = strtolower(str_replace(' ', '_', $request->name));
                $image_name = $name.'.'.$request->image->extension();
                $image_url = $request->image->storeAs('/public/images/testimonials', $image_name);
            } else $image_url = '';
            $testimonial = Testimonial::create(['name'=>$request->name, 'message'=>$request->message, 'image_url'=>substr($image_url, 7),]);
        });
        return $testimonial;
    }


    public function edit(Request $request, string $name) {
        $testimonial = Testimonial::where('name', $name)->first();
        DB::transaction(function () use ($request, &$testimonial) {
            $name = strtolower(str_replace(' ', '_', $request->name));
            $attributes = ['name'=>$request->name, 'message'=>$request->message,];

            if ($request->hasFile('image')) {
                $image_name = $name.'.'.$request->image->extension();
                $image_url = $request->image->storeAs('/public/images/testimonials', $image_name);
                $attributes = [...$attributes, 'image_url'=>substr($image_url, 7)];
            }
            $testimonial->update($attributes);
        });
        return $testimonial;
    }

    public function delete(Request $request, string $name) {
        $testimonial = Testimonial::where('name', $name)->first();
        DB::transaction(function () use ($request, &$testimonial) {
            $image_path = '/public/'.$testimonial->actual_image_url;
            $testimonial->delete();
            Storage::delete([$image_path]);
        });
        return response()->json(['status'=>'success'], 200,);
    }

    public function get_list(Request $request, string $count) {
        if ($count != 'all') {
            $testimonials = Testimonial::select(['name', 'message', 'image_url'])->take($count)->orderBy('id', 'desc')->get();
        } else {
            $testimonials = Testimonial::select(['name', 'message', 'image_url'])->orderBy('id', 'desc')->get();
        }
        return response()->json($testimonials, 200);
    }

    public function get(Request $request, string $name) {
        $testimonial = Testimonial::where('name', $name)->first();
        return response()->json($testimonial, 200);
    }

    // public function get_post(Request $request, string $heading) {
    //     $blog = Blog::where('heading', $heading)->first();
    //     $recent_posts = Blog::select(['heading'])->take(10)->orderBy('created_at', 'asc')->get();
    //     return response()->json(['blog'=> $blog, 'recent_posts'=>$recent_posts], 200);
    // }
}
