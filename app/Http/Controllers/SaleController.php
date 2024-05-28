<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\User;
use App\Models\Sale;
use App\Models\Cohort;
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
        $cohort = null;

        $type = match ($request->sale_type) {
             'cohort_sale'=> 1,
             'individual_course_sale'=> 2,
        };
        
        try {
            
            if ($type == 1) {
                $cohort = Cohort::where('name', $request->cohort_name)->select('id')->first();

                if (!$cohort) return response()->json(['status'=>'failed', 'message'=>'Could not find cohort with the given name'], 200);
            } else if ($type == 2) {
                $course = match ($request->course_type) {
                    'Certificate Course' => CertificateCourse::where('code', $request->course_identity)->select('id')->first(),
                    'Certification Course' => CertificationCourse::where('code', $request->course_identity)->select('id')->first(),
                    'Offshore Course' => OffshoreCourse::where('title', $request->course_identity)->select('id')->first(),
                };
                
                if (!$course) return response()->json(['status'=>'failed', 'message'=>'Could not find course with the given name'], 200);
            }
            
            
            DB::transaction(function () use ($request, $course, $type, $cohort) {
                $user = User::areStudents()->where('email', $request->student_email)->first();

                $student = new Student($user->makeVisible('id')->toArray());

                $attributes = ['sale_type_id'=>$type,'price'=>$request->price];

                
                if ($type == 1) {$attributes = [...$attributes, 'cohort_id'=>$cohort->id];}
                else if ($type == 2) {$attributes = [...$attributes, 'course_type'=>get_class($course), 'course_id'=>$course->id, ];}
                
                $sale = new Sale($attributes);
                
                $sale = $student->purchases()->save($sale);
                if ($type == 1) $cohort->students()->attach($student->id);
                
                $has_referral = $request->boolean('has_referral');
                if ($has_referral) {
                    $referral_code = ReferralCode::where('code', $request->referral_code)->first();
                    $referral = new Referral(['commission'=>$request->commission, 'code_id'=>$referral_code->id]);
                    $sale->referral()->save($referral);
                }
                
                // var_dump($sale->type); return null;

                $this->notify_student($student, $sale);

            });

            return response()->json(['status'=>'success', 'message'=>'Sale added'], 200);
        } catch (\Throwable $th) {
            return response()->json(['status'=>'failed', 'message'=>'Could not add sale. Please try again later'], 200);
        }

    }

    public function notify_student(Student $student, Sale $sale) {

        $api_endpoint = 'https://mitiget.com.ng/mailerapi/message/singlemail';

        $title = 'Congratulations on Your Purchase!';
        $message = view('emails.purchase', ['sale'=>$sale])->render();


        $data = [
            'title' => $title,
            
            'message' => $message,
            
            'email' => $student->email,
            
            'companyemail' => env('COMPANY_EMAIL'),
            
            'companypassword' => env('COMPANY_PASSWORD'),
        ];

        $response = \Illuminate\Support\Facades\Http::post($api_endpoint, $data);
        
        if (!$response->ok()) {
            throw new \Exception('couldn\'t send email');
        }
    }


    public function get_all(Request $request) {

        $sales = Sale::select(['id', 'price', 'date', 'user_id'])
        ->with(['student' => function ($query) {
            $query->select(['id', DB::raw('concat(first_name, " ", last_name) full_name')]);
        }])->get();

        return response()->json(['total_amount'=>Sale::sum('price'), 'sales'=>$sales], 200);
    }


    public function get(Request $request, int $id) {
        if (!$sale = Sale::get_sale($id)) return response()->json(['status'=>'failed', 'message'=>'Invalid id'], 200);

        // $sale = $sale->load(['student:first_name,last_name,email', 'referral'=>'referrer:first_name,last_name,email']);

        return response()->json(['status'=>'success' ,'sale'=>$sale], 200);
    }
}
