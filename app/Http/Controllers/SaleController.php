<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Sale;
use App\Models\CertificateCourse;
use App\Models\CertificationCourse;
use App\Models\OffshoreCourse;
use App\Models\Referral;
use App\Models\ReferralCode;
use App\Http\Requests\SaleRequest;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function store(SaleRequest $request) {
        
        $course = null;
        
        $course_identity = trim(preg_split('/-(?=[^-]*$)/', $request->course_name)[0]);

        
        
        
        try {
            
            
            $course = match ($request->course_type) {
                'Certificate Course' => CertificateCourse::where('code', $course_identity)->first(),
                'Certification Course' => CertificationCourse::where('code', $course_identity)->first(),
                'Offshore Course' => OffshoreCourse::where('title', $course_identity)->first(),
            };
            
            if (!$course) return response()->json(['status'=>'failed', 'message'=>'Could not find course with the given name'], 200);
            
            
            DB::transaction(function () use ($request, $course) {
                $student = Student::where('email', $request->student_email)->first();

                $sale = new Sale(['course_type'=>get_class($course), 'course_id'=>$course->id, 'price'=>$request->price]);

                $sale = $student->purchases()->save($sale);
                
                $has_referral = $request->boolean('has_referral');
                if ($has_referral) {
                    $referral_code = ReferralCode::where('code', $request->referral_code)->first();
                    $referral = new Referral(['commission'=>$request->commission, 'code_id'=>$referral_code->id, 'referrer_id'=>$referral_code->student->id]);
                    $sale->referral()->save($referral);
                }

            });

            return response()->json(['status'=>'success', 'message'=>'Sale added'], 200);
        } catch (\Throwable $th) {
            return response()->json(['status'=>'failed', 'message'=>'Could not add sale. Please try again later'], 200);
        }

    }


    public function get_all(Request $request) {


        // $sales = Sale::with('student')->get();

        $sales = Sale::get_all();


        return response()->json(['sales'=>$sales], 200);
    }


    public function get(Request $request, int $id) {
        if (!$sale = Sale::get_sale($id)) return response()->json(['status'=>'failed', 'message'=>'Invalid id'], 200);

        // $sale = $sale->load(['student:first_name,last_name,email', 'referral'=>'referrer:first_name,last_name,email']);

        return response()->json(['status'=>'success' ,'sale'=>$sale], 200);
    }
}
