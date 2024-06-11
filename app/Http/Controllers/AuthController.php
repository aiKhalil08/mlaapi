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
use App\Models\User;
use App\Traits\Recaptcha;


class AuthController extends Controller
{
    use Recaptcha;

    public function login_one(LoginRequest $request) {

        $validate_recaptcha = $this->validate_recaptcha();

        if ($validate_recaptcha[0] == 'failed') {
            return response()->json(['status'=> 'failed', 'message'=> $validate_recaptcha[1]], 200,);
        }

        $user = null;

        $user = User::where('email', $request->email)->first();
        if (!$user) return response()->json(['status'=> 'failed', 'message'=> 'Invalid username'], 200,);
        if (!Hash::check($request->password, $user->password)) return response()->json(['status'=> 'failed', 'message'=> 'Incorrect password'], 200,);

        // if ($user->hasAnyRole(['admin', 'super_admin'])) {
        //     $token = Auth::login($user);
        //     return $this->respondWithToken($token);
        // }

        $user->generateOTP();

        if ($user->sendOTP('login')) {
            return response()->json(['status'=> 'success', 'message'=> 'OTP Sent'], 200,);
        } else return response()->json(['status'=> 'failed', 'message'=> 'Unable to send OTP. Please try again later'], 200,);
        
    }

    public function resend_otp(Request $request, string $email) {
        $user = null;
        $user = User::where('email', $request->email)->first();
        

        $user->generateOTP();
        if ($user->sendOTP('login')) {
            return response()->json(['status'=> 'success', 'message'=> 'OTP Sent'], 200,);
        } else return response()->json(['status'=> 'failed', 'message'=> 'Unable to send OTP. Please try again later'], 200,);
        
    }

    public function login_two(Request $request) {
        $validator = Validator::make($request->all(), [
            'otp' => 'required|size:6',
            'email' => 'required|email'
        ]);

        $input = $validator->validated();

        $user = User::where('email', $input['email'])->first();
        
        $status = $user->validateOTP($input['otp']);

        if ($status == 'incorrect') return response()->json(['status'=> 'failed', 'message'=> 'The OTP you input is incorrect'], 200,);
        else if ($status == 'expired') return response()->json(['status'=> 'failed', 'message'=> 'The OTP you input has expired'], 200,);

        
        if ($token = Auth::login($user)) {
            return $this->respondWithToken($token);
        }
        return response()->json(['status'=> 'failed'], 200,);


    }


    protected function respondWithToken($token) {
        $user = Auth::user();

        $customClaims = ['first_name'=>$user->first_name, 'last_name'=>$user->last_name, 'email'=>$user->email, 'roles'=>$user->getRoleNames()->toArray(), 'email_verified'=>$user->hasVerifiedEmail(), 'image_url'=>$user->image_url];

        if ($user->hasRole('admin')) $customClaims = [...$customClaims, 'permissions'=>$user->getPermissionNames()->toArray()];


        $payload = JWTFactory::customClaims($customClaims)->make();
        $token = JWTAuth::encode($payload)->get();
        
        $data = [
            'status' => 'success',
            'access_token' => $token,
            'token_type' => 'bearer',
        ];

        if ($user->hasRole('super_admin')) $data = [...$data, 'is_super_admin'=>true];
        if ($user->hasRole('external_user')) $data = [...$data, 'is_external_user'=>true];

        if ($user->hasRole('student')) {
            // 'watchlist'=>base64_encode(json_encode($student->events()->pluck('name')->toArray())), 
            $student = new Student($user->toArray());
            $data = [...$data, 'cart'=>base64_encode(json_encode($student->cartedCoursesTitles())), 'affiliate'=>base64_encode(json_encode(['is_affiliate'=>$student->isAffiliate(), 'total_commission'=>$student->total_commission, 'referral_code'=>$student->referralCode?->code, 'referral_code_expired'=>$student->referralCodeHasExpired()]))];
        }

        return response()->json($data, 200);
    }


    public function confirmPassword(Request $request) {
        $user = auth()->user();

        if (Hash::check($request->password, $user->password)) return response()->json(['status'=>'success'], 200);
        else return response()->json(['status'=>'failed'], 200);
    }
}