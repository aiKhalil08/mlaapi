<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cohort;
use App\Models\User;
use App\Models\Student;
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
                $course = match ($request->course_type) {
                    'Certificate Course' => CertificateCourse::where('code', $request->course_identity)->select('id')->first(),
                    'Certification Course' => CertificationCourse::where('code', $request->course_identity)->select('id')->first(),
                    'Offshore Course' => OffshoreCourse::where('title', $request->course_identity)->select('id')->first(),
                };

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


    public function get_all(string $type = null) {

        $excluded_statuses = [];

        if ($type == 'for-sale') $excluded_statuses = [2, 3]; //exclude concluded and aborted cohorts when getting cohort names of adding sale
        
        $cohorts = Cohort::select(['name', 'status_id'])->whereNotIn('status_id', $excluded_statuses)->with(['status'=>function ($query) {
            $query->select(['id','name']);
        }])->get();

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

        $cohort = Cohort::select('id')->where('name', $name)->first();

        if (!$cohort) return response()->json(['status'=>'failed','message'=>'Cohort with provided name does not exist.'], 200);

        $cohort_id = $cohort->id;

        $ids_for_students_in_cohort = $cohort->students()->pluck('id')->toArray();


        $all_students = User::areStudents()->select(['id', 'first_name', 'last_name', 'email'])->get()->each(function ($student) use ($ids_for_students_in_cohort) {
            if (in_array($student->id, $ids_for_students_in_cohort)) $student->registration_status = 1;
            else $student->registration_status = 0;

        });


        return response()->json(['status'=>'success','students'=>$all_students->makeVisible('id')], 200);
    }

    public function updateStudents(Request $request, string $name) {
        
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


        return response()->json(['status'=>'success','cohort'=>[...collect($cohort->toArray())->except(['course_id']), 'course'=>$course->toArray()]], 200);
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

        $students = User::areStudents()->select('id', 'first_name', 'last_name', 'email')
        ->join('cohort_user AS cu', 'users.id', '=', 'cu.user_id')
        ->where('cu.cohort_id', $cohort_id)
        ->get()->map(function ($user) {
            return new Student($user->makeVisible('id')->toArray());
        });

        // var_dump($cohort_id); return null;

        $students->load(['certificate' => function ($query) use ($cohort_id) {
            $query->select(['url', 'user_id'])
                ->where('cohort_id', $cohort_id)
                ->limit(1);
        }]);

        return response()->json(['status'=>'success','students'=>$students], 200);
    }
}
