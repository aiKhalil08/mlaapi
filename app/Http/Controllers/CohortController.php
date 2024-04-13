<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cohort;
use App\Models\CertificateCourse;
use App\Models\CertificationCourse;
use App\Models\OffshoreCourse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;

class CohortController extends Controller
{
    public function store(Request $request) {
        $cohort = null;
        try {
            DB::transaction(function () use ($request, &$cohort) {
                // if ($request->hasFile('image')) {
                //     $name = strtolower(str_replace(' ', '_', $request->code));
                //     $image_name = $name.'.'.$request->image->extension();
                //     $image_url = $request->image->storeAs('/public/images/certificate_courses', $image_name);
                // } else $image_url = '';
                // if ($request->hasFile('schedule')) {
                //     $name = strtolower(str_replace(' ', '_', $request->code));
                //     $schedule_name = $name.'.'.$request->schedule->extension();
                //     $schedule_url = $request->schedule->storeAs('/public/schedule/certificate_courses', $schedule_name);
                // } else $schedule_url = '';
                // $course_identity = trim(preg_split('/-(?=[^-]*$)/', $request->course_name)[0]);
                $course = match ($request->course_type) {
                    'Certificate Course' => CertificateCourse::where('code', $request->course_identity)->select('id')->first(),
                    'Certification Course' => CertificationCourse::where('code', $request->course_identity)->select('id')->first(),
                    'Offshore Course' => OffshoreCourse::where('title', $request->course_identity)->select('id')->first(),
                };

                // var_dump([...$request->only(['name', 'start_date', 'end_date']), 'course_type'=>get_class($course), 'course_id'=>$course->id]); return null;

                $cohort = Cohort::create([...$request->only(['name', 'start_date', 'end_date',]), 'course_type'=>get_class($course), 'course_id'=>$course->id, 'duration'=>json_encode($request->duration)]);
            });
            return response()->json(['status'=>'success'], 200);
        } catch (\Throwable $th) {
            return response()->json(['status'=>'failed', 'message'=>'Something went wrong. Please try again.'], 200);
        }
    }

    public function edit(Request $request, string $name) {
        $cohort = Cohort::where('name', $name)->first();

        if (!$cohort) return response()->json(['status'=>'failed', 'message'=>'No cohort with such name.'], 200);

        try {
            DB::transaction(function () use ($request, &$cohort) {
                
                $course = match ($request->course_type) {
                    'Certificate Course' => CertificateCourse::where('code', $request->course_identity)->select('id')->first(),
                    'Certification Course' => CertificationCourse::where('code', $request->course_identity)->select('id')->first(),
                    'Offshore Course' => OffshoreCourse::where('title', $request->course_identity)->select('id')->first(),
                };

                $attributes = [...$request->only(['name', 'start_date', 'end_date',]), 'course_type'=>get_class($course), 'course_id'=>$course->id, 'duration'=>json_encode($request->duration)];

                $cohort->update($attributes);
            });
            return response()->json(['status'=>'success'], 200);
        } catch (\Throwable $th) {
            return response()->json(['status'=>'failed', 'message'=>'Something went wrong. Please try again.'], 200);
        }
    }   


    public function get_all() {
        $cohorts = Cohort::get_all();

        // $cohort = Cohort::with('status')->first();

        // $cohort->load('status');

        // var_dump($cohort->status); return null;

        // var_dump($cohorts); return null;

        return response()->json(['cohorts'=>$cohorts], 200);
    }

    public function get(Request $request, string $name) {
        $cohort = Cohort::where('name', $name)->first();

        if (!$cohort) return response()->json(['status'=>'failed', 'message'=>'No cohort with such name.'], 200);

        
        $course = $cohort->course;

        $students = $cohort->load('students:first_name,last_name,email')->students;


        $data = [...collect($cohort->toArray())->except(['course_id', 'course_type', 'status_id']), 'status'=>$cohort->status->name, 'course'=>['title'=>$course->title, 'image'=>$course->image_url, 'overview'=>str($course->overview)->limit(500, '...'), 'price'=>$course->price, 'type'=>get_class($course)], 'students'=>$students];

        if ($cohort->course_type != 'App\Models\OffshoreCourse') $data['course'] = [...$data['course'], 'code'=>$course->code];

        return response()->json(['status'=>'success', 'cohort'=>$data], 200);
    }


    public function all_students_showing_those_in_cohort(string $name) {

        $cohort_id = Cohort::select('id')->where('name', $name)->first()->id;

        if (!$cohort_id) return response()->json(['status'=>'failed','message'=>'Cohort with provided name does not exist.'], 200);

        $students = DB::select('select s.first_name, s.last_name, s.email, s.id, case when s.id in (select student_id from cohort_student where cohort_id = ?) then 1 else 0 end as registration_status from students as s', [$cohort_id]);

        return response()->json(['status'=>'success','students'=>$students], 200);
    }

    public function add_students(Request $request, string $name) {

        // var_dump($request->all()); return null;
        
        $cohort = Cohort::where('name', $name)->first();

        if (!$cohort) return response()->json(['status'=>'failed','message'=>'Cohort with provided name does not exist.'], 200);

        try {
            DB::transaction(function () use ($request, $cohort) {
                $students = $request->students ? array_keys($request->students) : null;
                $cohort->students()->sync($students);
            });
            return response()->json(['status'=>'success'], 200);
        } catch (\Throwable $th) {
            return response()->json(['status'=>'failed','message'=>'Something went wrong. Please try again'], 200);
        }



    }

    public function notify_students(Request $request, string $name) {

        $cohort = Cohort::where('name', $name)->first();


        if (!$cohort) return response()->json(['status'=>'failed','message'=>'Cohort with provided name does not exist.'], 200);

        $students = $cohort->students()->select(['first_name', 'last_name', 'email'])->get()->toArray();

        

        $template = $request->body;

        $pattern = '/{{(\w+)}}/';

        $matches = [];

        
        
        if (preg_match_all($pattern, $template, $matches)) {
            
            $acceptable_attributes = ['first_name', 'last_name', 'email'];
            $placeholders = $matches[0];
            $attributes = $matches[1];
            $replaced_placeholders = [];
            foreach ($attributes as $attribute) {
                if (!in_array($attribute, $acceptable_attributes)) return response()->json(['status'=>'failed', 'message'=>"Invalid attribute: $attribute"], 200);
            }
        }
        


        // var_dump($body); return null;


        // $message = preg_replace($pattern, $students[0][$placeholder], $template);


        $data = [
            'subject' => $request->subject,
            'template' => view('emails.notification', ['template'=>$template])->render(),
            'recipients' => $students,
        ];

        $api_endpoint = 'https://mitiget.com.ng/bulkmailer/dynamicbulk.php';

        try {
            $response = Http::asForm()->post($api_endpoint, $data);
            
            // return response()->json(['status'=>'success', 'message'=>'Students have been notified'], 200);

            if ($response->ok()) return response()->json(['status'=>'success', 'message'=>'Students have been notified'], 200);
            else return response()->json(['status'=>'failed', 'message'=>'Something went wrong. Please try again.'], 200);
        } catch (\Throwable $th) {
            if ($th instanceof ConnectionException) return response()->json(['status'=>'failed', 'message'=>'Please check your network connection and try again.']);
            return response()->json(['status'=>'failed', 'message'=>'Something went wrong. Please try again.']);
        }
        
    }


    public function get_cohort_for_edit(Request $request, string $name) {
        $cohort = Cohort::where('name', $name)->first();

        if (!$cohort) return response()->json(['status'=>'failed','message'=>'Cohort with provided name does not exist.'], 200);


        if ($cohort->course_type != 'App\\Models\\OffshoreCourse') $course = $cohort->course()->select(['code', 'title'])->first();
        else $course = $cohort->course()->select(['title'])->first();

        // $course_name['title'] = $course->title;


        // var_dump($cohort->toArray(), $course); return null;


        return response()->json(['status'=>'success','cohort'=>[...collect($cohort->toArray())->except(['course_id']), 'course'=>$course->toArray()]], 200);
    }


    public function get_names() {
        return Cohort::whereIn('status_id', [0,1, 2])->select(['name', 'status_id'])->get();
    }


    public function start(Request $request, string $name) {
        $cohort = Cohort::where('name', $name)->first();

        if (!$cohort) return response()->json(['status'=>'failed','message'=>'Cohort with provided name does not exist.'], 200);

        try {
            $start_date = \Carbon\Carbon::now();
            $cohort->update(['status_id'=>1, 'start_date'=>$start_date]);
            return response()->json(['status'=>'success', 'start_date'=>$start_date, 'message'=>'Cohort has been commenced.'], 200);
        } catch (\Throwable $th) {
            return response()->json(['status'=>'failed','message'=>'Something went wrong. Please try again'], 200);
        }
    }

    public function conclude(Request $request, string $name) {
        $cohort = Cohort::where('name', $name)->first();

        if (!$cohort) return response()->json(['status'=>'failed','message'=>'Cohort with provided name does not exist.'], 200);

        try {
            $end_date = \Carbon\Carbon::now();
            $cohort->update(['status_id'=>2, 'end_date'=>$end_date]);
            return response()->json(['status'=>'success', 'end_date'=>$end_date, 'message'=>'Cohort has been concluded.'], 200);
        } catch (\Throwable $th) {
            return response()->json(['status'=>'failed','message'=>'Something went wrong. Please try again'], 200);
        }
    }

    public function abort(Request $request, string $name) {
        $cohort = Cohort::where('name', $name)->first();

        if (!$cohort) return response()->json(['status'=>'failed','message'=>'Cohort with provided name does not exist.'], 200);

        try {
            $cohort->update(['status_id'=>3]);
            return response()->json(['status'=>'success','message'=>'Cohort has been aborted.'], 200);
        } catch (\Throwable $th) {
            return response()->json(['status'=>'failed','message'=>'Something went wrong. Please try again'], 200);
        }
    }

    public function delete(Request $request, string $name) {
        $cohort = Cohort::where('name', $name)->first();

        if (!$cohort) return response()->json(['status'=>'failed','message'=>'Cohort with provided name does not exist.'], 200);

        try {
            $cohort->delete();
            return response()->json(['status'=>'success','message'=>'Cohort has been deleted.'], 200);
        } catch (\Throwable $th) {
            return response()->json(['status'=>'failed','message'=>'Something went wrong. Please try again'], 200);
        }
    }

    public function get_students_certificates(string $name) {

        $cohort_id = Cohort::select('id')->where('name', $name)->first()->id;

        if (!$cohort_id) return response()->json(['status'=>'failed','message'=>'Cohort with provided name does not exist.'], 200);

        $students = DB::select('select s.first_name, s.last_name, s.email, (select url from certificates where student_id = s.id and certificates.cohort_id = ? limit 1) as certificate from students as s inner join cohort_student as cs on s.id = cs.student_id and cs.cohort_id = ?', [$cohort_id, $cohort_id]);

        // case when s.id in (select student_id from certificates where cohort_id = ?) then 1 else 0
        // ifnull(select url from certificates inner join students on certificates.student_id = s.id where certificates.cohort_id = ?, 0)
        // ifnull((select url from certificates where student_id = s.id and certificates.cohort_id = ? limit 1), "yoyo")

        return response()->json(['status'=>'success','students'=>$students], 200);
    }
}
