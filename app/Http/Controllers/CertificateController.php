<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\Certificate;
use App\Models\Cohort;
use App\Models\Student;
use App\Models\User;
use App\Models\CertificateCourse;
use App\Models\CertificationCourse;
use App\Models\OffshoreCourse;

class CertificateController extends Controller
{
    public function upload(Request $request) {
        try {
            $certificates = $request->certificates;
    
            $edits =  json_decode($request->edits);
    
            $type = match ($request->certificate_type) {
                'cohort_certificates' => 1,
                'individual_course_certificates' => 2,
            };
    
            if ($type == 1) {
                $base_name = Str::lower(Str::replace(' ', '_', $request->cohort_name));
                $cohort = Cohort::where('name', $request->cohort_name)->select('id')->first();
            } else if ($type == 2) {
                $base_name = Str::lower(Str::replace(' ', '_', $request->course_identity));
                $course = match ($request->course_type) {
                    'Certificate Course' => CertificateCourse::where('code', $request->course_identity)->select('id')->first(),
                    'Certification Course' => CertificationCourse::where('code', $request->course_identity)->select('id')->first(),
                    'Offshore Course' => OffshoreCourse::where('title', $request->course_identity)->select('id')->first(),
                };
            }
    
            
            $array = [];
            
            foreach ($certificates as $email => $certificateFile) {
                $file_name = $base_name;
                $extension = $certificateFile->extension();
                $file_name .= '@'.Str::lower($email).'.'.$extension;
                
                $to_be_edited = $edits && in_array($email, $edits);
                $old_certificate = null;
                
                $user = User::where('email', $email)->select('id')->first();

                $student = new Student($user->makeVisible('id')->toArray());
    
                if ($to_be_edited) {
                    if ($type == 1) $old_certificate = $student->certificates()->where('cohort_id', $cohort->id)->first();
                    else if ($type == 2) $old_certificate = $student->certificates()->where(['course_id'=> $course->id, 'course_type'=>get_class($course)])->first();
                    Storage::delete($old_certificate->url);
                }
                $url = $certificateFile->storeAs('images/certificates', $file_name);
                
                if ($to_be_edited) {
                    $old_certificate->update(['url'=> $url]);
                } else {
                    
                    $attributes = ['type_id'=>$type, 'user_id'=>$student->id, 'url'=>$url];
                    
                    if ($type == 1) $attributes = [...$attributes, 'cohort_id'=>$cohort->id];
                    else if ($type == 2) $attributes = [...$attributes, 'course_type'=>get_class($course), 'course_id'=>$course->id];
                    
                    Certificate::create($attributes);
                }
    
    
                
            }

            return response()->json(['status'=>'success'], 200);
        } catch (\Throwable $th) {
           return response()->json(['status'=>'failed', 'message'=>'Something went wrong. Please try again.'], 200);
        }
    }

    public function get(Request $request, string $type) {
        try {
            $student_email = $request->s;
            $student = Student::where('email', $student_email)->first();
    
            if (!$student) return response()->json(['status'=>'failed', 'message'=>'Not found.'], 200);
    
            $type = match ($type) {
                'cohort' => 1,
                'individual-course' => 2,
            };
    
            $certificate = null;
    
            if ($type == 1) {
                $cohort = Cohort::where('name', $request->cn)->first();
    
                if (!$cohort) return response()->json(['status'=>'failed', 'message'=>'Not found.'], 200);
    
                $certificate = $student->certificates()->where(['type_id'=>1, 'cohort_id'=>$cohort->id])->first()['url'];
            } else if ($type == 2) {
                $course = match ($request->ct) {
                    'Certificate Course' => CertificateCourse::where('code', $request->ci)->select('id')->first(),
                    'Certification Course' => CertificationCourse::where('code', $request->ci)->select('id')->first(),
                    'Offshore Course' => OffshoreCourse::where('title', $request->ci)->select('id')->first(),
                };
    
                if (!$course) return response()->json(['status'=>'failed', 'message'=>'Not found.'], 200);
    
                $certificate = $student->certificates()->where(['type_id'=>2, 'course_type'=>get_class($course), 'course_id'=>$course->id])->first()['url'];
            }
    
            return response()->json(['status'=>'success', 'certificate'=>['url'=>Storage::url($certificate)]], 200);
        } catch (\Throwable $th) {
            return response()->json(['status'=>'failed', 'message'=>'Something went wrong. Please try again.'], 200);
        }
    }



    public function downloadCertificate(Request $request) {
        $path = $request->path;
        return Storage::download($path);
    }
}
