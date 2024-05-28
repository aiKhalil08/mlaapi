<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\EventRegistration;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class EventController extends Controller
{
    public function store(Request $request) {

        try {
            // var_dump(collect($request->image)->toArray()); return null;
            $event = null;
            DB::transaction(function () use ($request, &$event) {
                if ($request->type == 'physical') {
                    $price = ['currency'=>$request->input('price.currency'), 'amount'=>$request->input('price.amount.physical')];
                } else {
                    $price = ['currency'=>$request->input('price.currency'), 'amount'=>$request->input('price.amount.virtual')];
                }

                $request->name = $this->stripTrailingFullstop($request->name);

                $attributes = ['name'=>$request->name, 'description'=>$request->description, 'date'=>$request->date, 'type'=>$request->type, 'price'=>$price, 'attendees'=>$request->attendees];

                $image_urls = [];
                if ($request->hasFile('image')) {
                    $i = 1;
                    foreach ($request->image as $file) {
                        $name = strtolower(str_replace(' ', '_', $request->name));
                        $image_name = $name.'_'.$i++.'.'.$file->extension();
                        $image_urls[] = $file->storeAs('images/events', $image_name);
                    }
                } else $image_urls[] = 'images/events/event_placeholder_'.rand(1,3).'.jpg';
                $attributes = [...$attributes, 'image_urls'=>$image_urls];

                $event = Event::create($attributes);
            });
            return response()->json(['status'=>'success'], 200);
            
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
                $request->name = $this->stripTrailingFullstop($request->name);
                $name = strtolower(str_replace(' ', '_', $request->name));
                $attributes = ['name'=>$request->name, 'description'=>$request->description, 'date'=>$request->date, 'type'=>$request->type, 'price'=>$price, 'attendees'=>$request->attendees];

                $image_urls = [];
                if ($request->hasFile('image')) {
                    $i = 1;
                    foreach ($request->image as $file) {
                        $name = strtolower(str_replace(' ', '_', $request->name));
                        $image_name = $name.'_'.$i++.'.'.$file->extension();
                        $image_urls[] = $file->storeAs('images/events', $image_name);
                    }
                    $attributes = [...$attributes, 'image_urls'=>$image_urls];
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
            $event->delete();
            // DB::transaction(function () use ($request, &$event) {
            //     // if ($event->image_url) $image_path = $event->image_path;
            //     // if ($event->image_url) Storage::delete([$image_path]);
            // });
            return response()->json(['status'=>'success'], 200,);
        } catch (\Throwable $th) {
            return response()->json(['status'=>'failed', 'message'=>'Something went wrong. Please try again.'], 200);
        }
    }

    public function get_list(Request $request, string $count) {
        if ($count != 'all') {
            $events = Event::select(['name', 'date', DB::raw('image_urls as image_url')])->orderBy('id', 'desc')->take($count)->get();
        } else {
            $events = Event::select(['name', 'date', DB::raw('image_urls as image_url')])->orderBy('id', 'desc')->get();
        }
        return response()->json($events, 200);
    }

    private function stripTrailingFullstop(string $value) {
        if (\substr($value, -1) === '.') {
            return \substr($value, 0, -1);
        }
        return $value;
    }

    public function get(Request $request, string $name) {

        $event = Event::where('name', $name)->first();

        if (!$event) return response()->json(['status'=>'failed', 'message'=>'No event with such name.'], 200);

        return response()->json(['status'=>'success', 'event'=>$event], 200);

    
    }

    public function register(Request $request, string $name) {
        $event = Event::where('name', $name)->first();

        if (!$event) return response()->json(['status'=>'failed', 'message'=>'Event not found'], 200);

        Validator::make($request->all(), [
            'first_name' => ['required'],
            'last_name' => ['required'],
            'email' => ['required', 'email'],
            'phone_number' => ['required', 'regex:/^(0\d{10}|[789]\d{9})$/'],
        ])->validate();

        try {
            DB::transaction(function () use ($request, &$event) {
                $event->registrations()->create($request->only('first_name', 'last_name', 'email', 'phone_number', 'message'));
            });
            return response()->json(['status'=>'success'], 200,);
        } catch (\Throwable $th) {
            return response()->json(['status'=>'failed', 'message'=>'Something went wrong. Please try again.'], 200);
        }
    }

    public function getRegistrations(string $name) {
        $event = Event::where('name', $name)->first();

        if (!$event) return response()->json(['status'=>'empty', 'message'=>'Event not found.'], 400);

        return response()->json(['status'=>'success', 'event_name'=>$event->name, 'registrants'=>$event->registrations], 200);
    }

    public function getRegistration(string $registration_id) {
        $registration = EventRegistration::find($registration_id);

        if (!$registration) return response()->json(['status'=>'empty', 'message'=>'Registration not found.'], 400);

        return response()->json(['status'=>'success', 'registration'=>$registration], 200);
    }
}