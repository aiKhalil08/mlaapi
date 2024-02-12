<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Blog;
use App\Models\Testimonial;
use Illuminate\Support\Facades\DB;

class ResourceController extends Controller
{
    public function get(Request $request) {


        $blogs = Blog::select(['heading', 'created_at'])->orderBy('created_at', 'asc')->get();
        $testimonials = Testimonial::select(['name', DB::raw('substring(message, 1, 25) as sub_message')])->orderBy('id', 'desc')->get();

        return response()->json(['blogs'=>$blogs, 'testimonials'=>$testimonials], 200,);
    }
}
