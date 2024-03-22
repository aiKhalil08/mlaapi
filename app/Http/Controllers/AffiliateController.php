<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Referral;
use App\Models\ReferralCode;

class AffiliateController extends Controller
{
    public function create_referral_code() {
        try {
            $student = auth()->user();


            // var_dump($student->generate_referral_code()); return null;
            $now = \Carbon\Carbon::now();
    
            $student->referral_codes()->create(['code'=>$student->generate_referral_code(), 'expires_at'=>$now->addMonths(5)]);


    
    
            return response()->json(['status'=>'success', 'affiliate'=>base64_encode(json_encode(['is_affiliate'=>$student->is_affiliate(), 'total_commission'=>$student->total_commission, 'referral_code'=>$student->referral_code->code, 'referral_code_expired'=>$student->referral_code_has_expired()]))], 200);
        } catch (\Throwable $th) {
            return response()->json(['status'=>'failed', 'message'=>'Something went wrong. Please try again'], 200);
        }
    }

    // public function renew_referral_code(Request $request) {
    //     try {
    //         $student = auth()->user();


    //         // var_dump($student->generate_referral_code()); return null;
    //         $now = \Carbon\Carbon::now();
    
    //         $student->referral_codes()->create(['code'=>$student->generate_referral_code(), 'expires_at'=>$now->addMonths(5)]);


    
    
    //         return response()->json(['status'=>'success', 'affiliate'=>base64_encode(json_encode(['is_affiliate'=>$student->is_affiliate(), 'referral_code'=>$student->referral_code->code, 'referral_code_expired'=>$student->referral_code_has_expired()]))], 200);
    //     } catch (\Throwable $th) {
    //         return response()->json(['status'=>'failed', 'message'=>'Something went wrong. Please try again'], 200);
    //     }
    // }

    public function load_affiliate_portal() {
        try {
            $student = auth()->user();

            if (!$student->is_affiliate()) return response()->json(['status'=>'failed', 'message'=>'User is not an affiliate'], 200);


            // // var_dump($student->generate_referral_code()); return null;
    
            // $student->referral_codes()->create(['code'=>$student->generate_referral_code()]);


    
    
            return response()->json(['status'=>'success', 'affiliate_portal'=>$student->affiliate_portal()], 200);
        } catch (\Throwable $th) {
            return response()->json(['status'=>'failed', 'message'=>'Something went wrong. Please try again'], 200);
        }
    }


    public function add_referral(ReferralRequest $request) {

        Referral::add($request->only(['referrer_id', 'buyer_id', 'code_id', 'course_id', 'course_type']));
    }


    public function fetch_affiliate(Request $request, string $code) {
        try {
            $code = ReferralCode::where('code', $code)->first();

            if (!$code) return response()->json(['status'=> 'failed', 'message'=> 'Invalid referral code'], 200);

            $student = $code->student;

            if ($student->referral_code->code != $code->code) return response()->json(['status'=> 'failed', 'message'=> 'Outdated referral code'], 200);

            if (\Carbon\Carbon::now() > $code->expires_at) return response()->json(['status'=> 'failed', 'message'=> 'Referral code has expired'], 200);


            return response()->json(['status'=>'success', 'affiliate'=> ['name'=> $student->name, 'percentage'=> $code->commission, 'email'=>$student->email]], 200);
        } catch (\Throwable $th) {
            return response()->json(['status'=>'failed', 'message'=>'Something went wrong. Please try again.'], 200);
        }
    }
}
