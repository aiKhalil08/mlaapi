<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class EventController extends Controller
{
    public function store(Request $request) {
        $event = null;
        DB::transaction(function () use ($request, &$event) {
            if ($request->hasFile('image')) {
                $name = strtolower(str_replace(' ', '_', $request->name));
                $image_name = $name.'.'.$request->image->extension();
                $image_url = $request->image->storeAs('/public/images/events', $image_name);
            } else $image_url = '';
            if ($request->type == 'physical') {
                $price = ['currency'=>$request->input('price.currency'), 'amount'=>$request->input('price.amount.physical')];
            } else {
                $price = ['currency'=>$request->input('price.currency'), 'amount'=>$request->input('price.amount.virtual')];
            }
            $event = Event::create(['name'=>$request->name, 'description'=>$request->description, 'date'=>json_encode($request->date), 'type'=>$request->type, 'price'=>json_encode($price), 'attendees'=>json_encode($request->attendees), 'image_url'=>substr($image_url, 7),]);
        });
        return $event;
    }


    public function edit(Request $request, string $name) {
        $event = Event::where('name', $name)->first();
        DB::transaction(function () use ($request, &$event) {
            if ($request->type == 'physical') {
                $price = ['currency'=>$request->input('price.currency'), 'amount'=>$request->input('price.amount.physical')];
            } else {
                $price = ['currency'=>$request->input('price.currency'), 'amount'=>$request->input('price.amount.virtual')];
            }
            $name = strtolower(str_replace(' ', '_', $request->name));
            $attributes = ['name'=>$request->name, 'description'=>$request->description, 'date'=>json_encode($request->date), 'type'=>$request->type, 'price'=>json_encode($price), 'attendees'=>json_encode($request->attendees)];

            if ($request->hasFile('image')) {
                $image_name = $name.'.'.$request->image->extension();
                $image_url = $request->image->storeAs('/public/images/events', $image_name);
                $attributes = [...$attributes, 'image_url'=>substr($image_url, 7)];
            }
            $event->update($attributes);
        });
        return $event;
    }

    public function delete(Request $request, string $name) {
        $event = Event::where('name', $name)->first();
        DB::transaction(function () use ($request, &$event) {
            $image_path = '/public/'.$event->actual_image_url;
            $event->delete();
            Storage::delete([$image_path]);
        });
        return response()->json(['status'=>'success'], 200,);
    }

    public function get_list(Request $request, string $count) {
        if ($count != 'all') {
            $events = Event::select(['name', 'date', 'image_url'])->orderBy('id', 'desc')->take($count)->get();
        } else {
            $events = Event::select(['name', 'date', 'image_url'])->orderBy('id', 'desc')->get();
        }
        return response()->json($events, 200);
    }

    public function get(Request $request, string $name) {
        $event = Event::where('name', $name)->first();
        return response()->json($event, 200);
    }

    // public function get_post(Request $request, string $heading) {
    //     $blog = Blog::where('heading', $heading)->first();
    //     $recent_posts = Blog::select(['heading'])->take(10)->orderBy('created_at', 'asc')->get();
    //     return response()->json(['blog'=> $blog, 'recent_posts'=>$recent_posts], 200);
    // }
}