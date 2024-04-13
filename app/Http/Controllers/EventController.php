<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class EventController extends Controller
{
    public function store(Request $request) {

        try {
            $event = null;
            DB::transaction(function () use ($request, &$event) {
                if ($request->type == 'physical') {
                    $price = ['currency'=>$request->input('price.currency'), 'amount'=>$request->input('price.amount.physical')];
                } else {
                    $price = ['currency'=>$request->input('price.currency'), 'amount'=>$request->input('price.amount.virtual')];
                }

                $attributes = ['name'=>$request->name, 'description'=>$request->description, 'date'=>json_encode($request->date), 'type'=>$request->type, 'price'=>json_encode($price), 'attendees'=>json_encode($request->attendees)];

                if ($request->hasFile('image')) {
                    $name = strtolower(str_replace(' ', '_', $request->name));
                    $image_name = $name.'.'.$request->image->extension();
                    $image_url = $request->image->storeAs('images/events', $image_name);
                } else $image_url = 'images/events/event_placeholder_'.rand(1,3).'.jpg';
                $attributes = [...$attributes, 'image_url'=>$image_url];

                $event = Event::create($attributes);
            });
            return response()->json(['status'=>'success'], 200);
            // return $course;
        } catch (\Throwable $th) {
            return response()->json(['status'=>'failed', 'message'=>'Something went wrong. Please try again.'], 200);
        }
    }


    public function edit(Request $request, string $name) {

        try {
            
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
                    $image_url = $request->image->storeAs('images/events', $image_name);
                    $attributes = [...$attributes, 'image_url'=>$image_url];
                }
                $event->update($attributes);
            });
            return response()->json(['status'=>'success'], 200);
        } catch (\Throwable $th) {
            return response()->json(['status'=>'failed', 'message'=>'Something went wrong. Please try again.'], 200);
        }


    }

    public function delete(Request $request, string $name) {

        try {
            
            $event = Event::where('name', $name)->first();
            DB::transaction(function () use ($request, &$event) {
                if ($event->image_url) $image_path = $event->image_path;
                $event->delete();
                if ($event->image_url) Storage::delete([$image_path]);
            });
            return response()->json(['status'=>'success'], 200,);
        } catch (\Throwable $th) {
            return response()->json(['status'=>'failed', 'message'=>'Something went wrong. Please try again.'], 200);
        }
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

        if (!$event) return response()->json(['status'=>'failed', 'message'=>'No event with such name.'], 200);

        return response()->json(['status'=>'success', 'event'=>$event], 200);

    
    }

    // public function get_post(Request $request, string $heading) {
    //     $blog = Blog::where('heading', $heading)->first();
    //     $recent_posts = Blog::select(['heading'])->take(10)->orderBy('created_at', 'asc')->get();
    //     return response()->json(['blog'=> $blog, 'recent_posts'=>$recent_posts], 200);
    // }
}