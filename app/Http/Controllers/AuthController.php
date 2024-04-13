<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\LoginRequest;
use Tymon\JWTAuth\Facades\JWTFactory;
use JWTAuth;
use App\Models\Student;
use App\Traits\Recaptcha;

class AuthController extends Controller
{
    use Recaptcha;
    

    public function admin_login(LoginRequest $request) {
        $guard = 'admin-jwt';
        $credentials = ['username' => $request->username, 'password' => $request->password];
        if ($token = Auth::guard($guard)->attempt($credentials)) {
            return $this->respondWithToken($token, $guard);
        }
        return response()->json(['status'=> 'failed'], 200,);
    }

    public function login_one(LoginRequest $request, string $type) {

        // return $this->validate_recaptcha();
        $validate_recaptcha = $this->validate_recaptcha();

        if ($validate_recaptcha[0] == 'failed') {
            return response()->json(['status'=> 'failed', 'message'=> $validate_recaptcha[1]], 200,);
        }

        $user = null;
        if ($type == 'student') {
            $user = Student::where('email', $request->email)->first();
            if (!$user) return response()->json(['status'=> 'failed', 'message'=> 'Invalid username'], 200,);
            if (!Hash::check($request->password, $user->password)) return response()->json(['status'=> 'failed', 'message'=> 'Incorrect password'], 200,);
        } else if ($type == 'tutor') {
            // $user = Student::where('email', $input['email']);
            // $guard = 'student-jwt';
        }

        $user->generate_otp();

        // var_dump('are you here'); return null;

        // $user->send_otp('login'); return null;
        if ($user->send_otp('login')) {
            return response()->json(['status'=> 'success', 'message'=> 'OTP Sent'], 200,);
        } else return response()->json(['status'=> 'failed', 'message'=> 'Unable to send OTP. Please try again later'], 200,);
        
    }

    public function resend_otp(Request $request, string $type, string $email) {
        $user = null;
        if ($type == 'student') {
            $user = Student::where('email', $request->email)->first();
        } else if ($type == 'tutor') {
            // $user = Student::where('email', $input['email']);
            // $guard = 'student-jwt';
        }

        $user->generate_otp();
        if ($user->send_otp()) {
            return response()->json(['status'=> 'success', 'message'=> 'OTP Sent'], 200,);
        } else return response()->json(['status'=> 'failed', 'message'=> 'Unable to send OTP. Please try again later'], 200,);
        
    }

    public function login_two(Request $request, string $type) {
        $validator = Validator::make($request->all(), [
            'otp' => 'required|size:6',
            'email' => 'required|email'
        ]);

        $input = $validator->validated();

        if ($type == 'student') {
            $user = Student::where('email', $input['email'])->first();
            $guard = 'student-jwt';
        } else if ($type == 'tutor') {
            // $user = Student::where('email', $input['email']);
            // $guard = 'student-jwt';
        }
        $status = $user->validate_otp($input['otp']);

        if ($status == 'incorrect') return response()->json(['status'=> 'failed', 'message'=> 'The OTP you input is incorrect'], 200,);
        else if ($status == 'expired') return response()->json(['status'=> 'failed', 'message'=> 'The OTP you input has expired'], 200,);

        
        if ($token = Auth::guard($guard)->login($user)) {
            return $this->respondWithToken($token, $guard);
        }
        return response()->json(['status'=> 'failed'], 200,);


    }

    // public function refresh(Request $request) {
        // if ($refresh_token = $request->refresh) {
        //     $refresh_model = RefreshToken::where('token', $refresh_token)->first();
        //     $owner = $refresh_model->tokenable;
        //     $token = auth()->guard('student-jwt')->login($owner);
        //     // var_dump($token);return null;
        //     return $this->respondWithToken($token);
    //     }
    // }

    protected function respondWithToken($token, $guard) {
        $user = Auth::guard($guard)->user();
        $customClaims = ['first_name'=>$user->first_name, 'last_name'=>$user->last_name, 'email'=>$user->email, 'role'=>$user->type];
        if ($user->type != 'admin') $customClaims = [...$customClaims, 'email_verified'=>$user->hasVerifiedEmail(), 'image_url'=>$user->image_url];

        $payload = JWTFactory::customClaims($customClaims)->make();
        $token = JWTAuth::encode($payload)->get();
        
        $data = [
            'status' => 'success',
            'access_token' => $token,
            'token_type' => 'bearer',
        ];

        if ($user->type == 'student') $data = [...$data, 'cart'=>base64_encode(json_encode($user->carted_courses_titles())), 'watchlist'=>base64_encode(json_encode($user->events()->pluck('name')->toArray())), 'affiliate'=>base64_encode(json_encode(['is_affiliate'=>$user->is_affiliate(), 'total_commission'=>$user->total_commission, 'referral_code'=>$user->referral_code?->code, 'referral_code_expired'=>$user->referral_code_has_expired()]))];

        return response()->json($data, 200);
    }


    public function confirm_admin_password(Request $request) {
        $user = auth()->user();

        if (Hash::check($request->password, $user->password)) return response()->json(['status'=>'success'], 200);
        else return response()->json(['status'=>'failed'], 200);
    }

    public function confirm_student_password(Request $request) {
        $user = auth()->user();

        if (Hash::check($request->password, $user->password)) return response()->json(['status'=>'success'], 200);
        else return response()->json(['status'=>'failed'], 200);
    }

    
}