<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Event;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\CreateStudentRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTFactory;
use JWTAuth;

class StudentController extends Controller
{
    public function store(CreateStudentRequest $request) {
        $student = null;
        try {
            DB::transaction(function () use ($request, &$student) {
                $student = Student::create([...$request->only(['first_name', 'last_name', 'phone_number', 'email']), 'password'=>bcrypt($request->password)]);
                
                if (!$student) throw new \Exception('Something went wrong. Please try again.');
        
                $student->generate_otp();
        
                if (!$student->send_otp('verification')) throw new \Exception('Unable to send OTP. Please try again.');
            });
            return response()->json(['status'=> 'success', 'message'=> 'OTP Sent'], 200,);
        } catch (\Exception $e) {
            return response()->json(['status'=> 'failed', 'message'=> $e->getMessage()], 200,);
        }
        
    }

    public function send_otp(Request $request) {
        $student = Student::where('email', $request->email)->first();

        $student->generate_otp();

        if ($student->send_otp('verification')) {
            return response()->json(['status'=> 'success', 'message'=> 'OTP Sent'], 200,);
        } else return response()->json(['status'=> 'failed', 'message'=> 'Unable to send OTP. Please try again later'], 200,);
    }

    public function confirm_email(Request $request) {
        $validator = Validator::make($request->all(), [
            'otp' => 'required|size:6',
            'email' => 'required|email'
        ]);

        $input = $validator->validated();
        $student = Student::where('email', $input['email'])->first();
        $guard = 'student-jwt';

        $status = $student->validate_otp($input['otp']);

        if ($status == 'incorrect') return response()->json(['status'=> 'failed', 'message'=> 'The OTP you input is incorrect'], 200,);
        else if ($status == 'expired') return response()->json(['status'=> 'failed', 'message'=> 'The OTP you input has expired'], 200,);

        
        if ($token = Auth::guard($guard)->login($student)) {
            $student->email_verified = 1;
            $student->save();
            $student->refresh();
            return $this->respondWithToken($token, $guard);
        }
        return response()->json(['status'=> 'failed'], 200,);

    }

    protected function respondWithToken($token, $guard) {
        $user = Auth::guard($guard)->user();
        $customClaims = ['first_name'=>$user->first_name, 'last_name'=>$user->last_name, 'email'=>$user->email,];
        if ($user->type != 'admin') $customClaims = [...$customClaims, 'email_verified'=>$user->hasVerifiedEmail(), 'image_url'=>$user->image_url];

        $payload = JWTFactory::customClaims($customClaims)->make();
        $token = JWTAuth::encode($payload)->get();
        
        $data = [
            'status' => 'success',
            'access_token' => $token,
            'token_type' => 'bearer',
        ];

        return response()->json($data, 200);
    }

    public function get_cart(Request $request) {
        // var_dump('are you here'); return null;
        $student = auth()->user();

        return response()->json(['cart' => $student->carted_courses()], 200);
    }

    public function get_carted_course(Request $request) {

        $student = auth()->user();

        if ($course = $student->get_carted_course($request->type, $request->identity)) {
            return response()->json(['status'=>'success', 'course'=> $course[0]], 200);
        } else return response()->json(['status'=>'failed',], 200);
    }

    public function get_profile() {
        $student = auth()->user();

        return response()->json(['profile'=>$student], 200,);
    }

    public function update_profile(Request $request) {
        $student = auth()->user();

        try {
            DB::transaction(function () use ($request, &$student) {
                $attributes = $request->only(['first_name', 'last_name', 'email', 'phone_number', 'home_address', 'bio']);
                if ($request->hasFile('image')) {
                    $image_name = $request->email.'.'.$request->image->extension();
                    $image_url = $request->image->storeAs('/public/images/students', $image_name);
                    $attributes = [...$attributes, 'image_url'=>substr($image_url, 7)];
                }
                $student->update($attributes);
                // $student->refresh();
            });
        } catch (\Exception $e) {
            return response()->json(['status'=>'failed', 'message'=>'Something went wrong. Please try again'], 200,);
        }

        return $this->respondWithToken(auth()->getToken(), 'student-jwt');
    }


    public function add_event_to_watchlist(Request $request) {
        $event = Event::where('name', $request->event_name)->first();
        $student = auth()->user();


        try {
            $student->events()->attach($event->id);
            // $student->refresh();
            return response()->json(['status'=>'success', 'message'=>'Event added to watchlist.', 'watchlist'=>base64_encode(json_encode($student->events()->pluck('name')->toArray()))], 200,);
        } catch (\Exception $th) {
            return response()->json(['status'=>'failed', 'message'=>'Something went wrong. Please try again'], 200,);
        }


    }

    public function get_event_watchlist() {

        $student = auth()->user();
        

        $watchlistWithPivot = $student->events()->select('name', 'type', 'image_url')->get()->toArray();


        $watchlist = array_map(function($item) {return ['name' => $item['name'], 'type'=>$item['type'], 'image_url'=>$item['image_url']];}, $watchlistWithPivot);

       


        return response()->json(['watchlist'=>$watchlist], 200);

    }

    public function get_event_watchlist_event(Request $request, string $event) {
        $student = auth()->user();

        if (!$event = $student->events()->where('name', $event)->first()) return response()->json(['status'=>'failed', 'message'=>'No such event'], 200, $headers);

        return response()->json(['status'=>'success', 'event'=>$event], 200,);
    }


    public function fetch_name(Request $request, string $email) {
        $student = null;
        if (!$student = Student::where('email', $email)->first()) return response()->json(['status'=> 'failed', 'message'=> 'student with provided email not found'], 200);

        return response()->json(['status'=>'success', 'message'=>'found', 'name'=>$student->name], 200);
    }

}
