<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Blog;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class BlogController extends Controller
{
    public function store(Request $request) {


        try {
            $blog = null;
            DB::transaction(function () use ($request, &$blog) {
                $attributes = ['heading'=>$request->heading, 'content'=>$request->content];
                if ($request->hasFile('image')) {
                    $name = strtolower(str_replace(' ', '_', $request->heading));
                    $image_name = $name.'.'.$request->image->extension();
                    $image_url = $request->image->storeAs('images/blogs', $image_name);
                    $attributes = [...$attributes, 'image_url'=>$image_url];
                }
                $blog = Blog::create($attributes);
            });
            return response()->json(['status'=>'success'], 200);
        } catch (\Throwable $th) {
            return response()->json(['status'=>'failed', 'message'=>'Something went wrong. Please try again.'], 200);
        }

    }


    public function edit(Request $request, string $heading) {

        try {
            
            $blog = Blog::where('heading', $heading)->first();
            DB::transaction(function () use ($request, &$blog) {
                $name = strtolower(str_replace(' ', '_', $request->heading));
                $attributes = ['heading'=>$request->heading, 'content'=>$request->content,];

                if ($request->hasFile('image')) {
                    $image_name = $name.'.'.$request->image->extension();
                    $image_url = $request->image->storeAs('images/blogs', $image_name);
                    $attributes = [...$attributes, 'image_url'=>$image_url];
                }
                $blog->update($attributes);
            });
            return response()->json(['status'=>'success'], 200);
        } catch (\Throwable $th) {
            return response()->json(['status'=>'failed', 'message'=>'Something went wrong. Please try again.'], 200);
        }

    }

    public function delete(Request $request, string $heading) {


        try {
            
            $blog = Blog::where('heading', $heading)->first();
            DB::transaction(function () use ($request, &$blog) {
                if ($blog->image_url) $image_path = $blog->image_path;
                $blog->delete();
                if ($blog->image_url) Storage::delete([$image_path]);
            });
            return response()->json(['status'=>'success'], 200,);
        } catch (\Throwable $th) {
            return response()->json(['status'=>'failed', 'message'=>'Something went wrong. Please try again.'], 200);
        }

    }

    public function get_list(Request $request, string $count) {
        if ($count != 'all') {
            $blogs = Blog::select(['heading', 'content', 'image_url', 'author', 'created_at'])->take($count)->orderBy('created_at', 'desc')->get();
        } else {
            $blogs = Blog::select(['heading', 'content', 'image_url', 'author', 'created_at'])->orderBy('created_at', 'desc')->get();
        }
        return response()->json($blogs, 200);
    }

    public function get(Request $request, string $heading) {

        $blog = Blog::where('heading', $heading)->first();

        if (!$blog) return response()->json(['status'=>'failed', 'message'=>'No blog with such heading.'], 200);

        return response()->json(['status'=>'success', 'blog'=>$blog], 200);
    }

    public function get_post(Request $request, string $heading) {

        $blog = Blog::where('heading', $heading)->first();

        if (!$blog) return response()->json(['status'=>'failed', 'message'=>'No blog with such heading.'], 200);

        $recent_posts = Blog::select(['heading'])->take(10)->orderBy('created_at', 'desc')->get();


        return response()->json(['status'=>'success', 'blog'=>$blog, 'recent_posts'=>$recent_posts], 200);
    }
}
