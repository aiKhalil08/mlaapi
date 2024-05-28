<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Referral;
use App\Models\Student;
use App\Models\User;
use App\Models\ReferralCode;
use Illuminate\Contracts\Database\Eloquent\Builder;

class AffiliateController extends Controller
{
    public function create_referral_code() {
        try {
            $user = auth()->user();

            $user->referralCodes()->update(['validity'=>0]);

            // var_dump($user->generate_referral_code()); return null;
            $now = \Carbon\Carbon::now();
    
            $user->referralCodes()->create(['code'=>$user->generateReferralCode(), 'expires_at'=>$now->addMonths(5), 'validity'=>1]);
    
    
            return response()->json(['status'=>'success', 'affiliate'=>base64_encode(json_encode(['is_affiliate'=>$user->isAffiliate(), 'total_commission'=>$user->total_commission, 'referral_code'=>$user->referral_code->code, 'referral_code_expired'=>$user->referralCodeHasExpired()]))], 200);
        } catch (\Throwable $th) {
            return response()->json(['status'=>'failed', 'message'=>'Something went wrong. Please try again'], 200);
        }
    }

    public function renew_referral_code(Request $request) {
        try {
            $user = auth()->user();


            $now = \Carbon\Carbon::now();

            $user->referralCodes()->update(['validity'=>0]);
    
            $user->referralCodes()->create(['code'=>$user->generateReferralCode(), 'expires_at'=>$now->addMonths(5), 'validity'=>1]);


    
    
            return response()->json(['status'=>'success', 'referral_code'=>$user->referralCode->code], 200);
        } catch (\Throwable $th) {
            return response()->json(['status'=>'failed', 'message'=>'Something went wrong. Please try again'], 200);
        }
    }

    public function load_affiliate_portal() {
        try {
            $user = auth()->user();

            if (!$user->isAffiliate()) return response()->json(['status'=>'failed', 'message'=>'Not affiliate'], 200);

    
    
            return response()->json(['status'=>'success', 'affiliate_portal'=>$user->affiliatePortal()], 200);
        } catch (\Throwable $th) {
            return response()->json(['status'=>'failed', 'message'=>'Something went wrong. Please try again'], 200);
        }
    }

    public function get(Request $request, string $email) {
        // try {
            $user = User::areStudents()->where('email', $email)->first();

            if (!$user) return response()->json(['status'=>'failed', 'message'=>'User with given email not found'], 200);

            if (!$user->isAffiliate()) return response()->json(['status'=>'failed', 'message'=>'User is not an affiliate'], 200);

    
    
            return response()->json(['status'=>'success', 'affiliate_portal'=>$user->affiliatePortal()], 200);
        // } catch (\Throwable $th) {
        //     return response()->json(['status'=>'failed', 'message'=>'Something went wrong. Please try again'], 200);
        // }
    }


    public function add_referral(ReferralRequest $request) {

        Referral::add($request->only(['referrer_id', 'buyer_id', 'code_id', 'course_id', 'course_type']));
    }


    public function fetch_affiliate(Request $request, string $code) {
        try {
            $code = ReferralCode::whereRaw('BINARY code = ?', [$code])->first();

            if (!$code) return response()->json(['status'=> 'failed', 'message'=> 'Invalid referral code'], 200);

            $student = $code->owner;

            if ($student->referralCode->code != $code->code) return response()->json(['status'=> 'failed', 'message'=> 'Outdated referral code'], 200);

            if (\Carbon\Carbon::now() > $code->expires_at) return response()->json(['status'=> 'failed', 'message'=> 'Referral code has expired'], 200);


            return response()->json(['status'=>'success', 'affiliate'=> ['name'=> $student->name, 'percentage'=> $code->commission, 'email'=>$student->email]], 200);
        } catch (\Throwable $th) {
            return response()->json(['status'=>'failed', 'message'=>'Something went wrong. Please try again.'], 200);
        }
    }

    public function get_all() {
        $affiliates = User::areAffiliates()
        ->with('referralCode')
        ->get()
        ->map(function ($user) {
            return ['first_name'=>$user->first_name, 'last_name'=>$user->last_name, 'email'=>$user->email, 'referral_code'=>$user->referral_code_string];
        });

        return response()->json(['affiliates'=>$affiliates], 200);
    }
}
