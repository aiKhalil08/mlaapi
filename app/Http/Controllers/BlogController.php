<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Blog;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class BlogController extends Controller
{
    public function store(Request $request) {
        $blog = null;
        DB::transaction(function () use ($request, &$blog) {
            if ($request->hasFile('image')) {
                $name = strtolower(str_replace(' ', '_', $request->heading));
                $image_name = $name.'.'.$request->image->extension();
                $image_url = $request->image->storeAs('/public/images/blogs', $image_name);
            } else $image_url = '';
            $blog = Blog::create(['heading'=>$request->heading, 'content'=>$request->content, 'image_url'=>substr($image_url, 7),]);
        });
        return $blog;
    }


    public function edit(Request $request, string $heading) {
        $blog = Blog::where('heading', $heading)->first();
        DB::transaction(function () use ($request, &$blog) {
            $name = strtolower(str_replace(' ', '_', $request->heading));
            $attributes = ['heading'=>$request->heading, 'content'=>$request->content,];

            if ($request->hasFile('image')) {
                $image_name = $name.'.'.$request->image->extension();
                $image_url = $request->image->storeAs('/public/images/blogs', $image_name);
                $attributes = [...$attributes, 'image_url'=>substr($image_url, 7)];
            }
            $blog->update($attributes);
        });
        return $blog;
    }

    public function delete(Request $request, string $heading) {
        $blog = Blog::where('heading', $heading)->first();
        DB::transaction(function () use ($request, &$blog) {
            $image_path = '/public/'.$blog->actual_image_url;
            $blog->delete();
            Storage::delete([$image_path]);
        });
        return response()->json(['status'=>'success'], 200,);
    }

    public function get_list(Request $request, string $count) {
        if ($count != 'all') {
            $blogs = Blog::select(['heading', 'content', 'image_url', 'author', 'created_at'])->take($count)->orderBy('created_at', 'asc')->get();
        } else {
            $blogs = Blog::select(['heading', 'content', 'image_url', 'author', 'created_at'])->orderBy('created_at', 'asc')->get();
        }
        return response()->json($blogs, 200);
    }

    public function get(Request $request, string $heading) {
        $blog = Blog::where('heading', $heading)->first();
        return response()->json($blog, 200);
    }

    public function get_post(Request $request, string $heading) {
        $blog = Blog::where('heading', $heading)->first();
        $recent_posts = Blog::select(['heading'])->take(10)->orderBy('created_at', 'asc')->get();
        return response()->json(['blog'=> $blog, 'recent_posts'=>$recent_posts], 200);
    }
}
