<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\User;
use App\Models\Cart;
use App\Models\Event;
use App\Models\CertificateCourse;
use App\Models\CertificationCourse;
use App\Models\OffshoreCourse;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\CreateStudentRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTFactory;
use JWTAuth;
use App\Traits\Recaptcha;
use Illuminate\Contracts\Database\Eloquent\Builder;

class StudentController extends Controller
{

    use Recaptcha;

    public function store(CreateStudentRequest $request) {

        $validate_recaptcha = $this->validate_recaptcha();

        if ($validate_recaptcha[0] == 'failed') {
            return response()->json(['status'=> 'failed', 'message'=> $validate_recaptcha[1]], 200,);
        }

        $student = null;
        try {
            DB::transaction(function () use ($request, &$student) {
                $student = Student::create([...$request->only(['first_name', 'last_name', 'phone_number', 'email']), 'password'=>bcrypt($request->password)]);
                
                if (!$student) throw new \Exception('Something went wrong. Please try again.');
        
                $student->generate_otp();


                $student->send_welcome_email();
        
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


    protected function respondWithToken($token) {
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

        return response()->json($data, 200);
    }




    public function getCart(Request $request) {
        $student = new Student(auth()->user()->makeVisible('id')->toArray());

        if ($student->carts->isEmpty()) return response()->json(['status'=>'empty', 'message'=>'You haven\'t carted any course.'], 200);

        return response()->json(['courses' => $student->cartedCourses()], 200);
    }

    public function getEnrolledCourses(Request $request) {
        $student = new Student(auth()->user()->makeVisible('id')->toArray());


        $purchases = $student->purchases;

        // var_dump($purchases); return null;


        if ($purchases->isEmpty()) return response()->json(['status'=>'empty', 'message'=>'You haven\'t enrolled for any course.'], 200);

        $courses = [];

        foreach ($purchases as $purchase) {
            $array = [];
            if ($course = $purchase->course) {
                $array['enrollment_type'] = 'individual';
            } else if ($course = $purchase->cohort->course) {
                $array['enrollment_type'] = 'cohort';
                $array['cohort_name'] = $purchase->cohort->name;
            }
            $course = $purchase->course ?: $purchase->cohort->course;

            // $array = new stdClass();
            // $a = [];
            $array['title'] = $course->title;

            $array['image_url'] = $course->image_url;
            $array['number_of_modules'] = count($course->modules);
            
            
            if (get_class($course) == 'App\Models\CertificateCourse') {
                $array['code'] = $course->code;
                $courses['certificate_courses'][] = $array;
            } else if (get_class($course) == 'App\Models\CertificationCourse') {
                $array['code'] = $course->code;
                $courses['certification_courses'][] = $array;
            } else if (get_class($course) == 'App\Models\OffshoreCourse') {
                $array['title'] = $course->title;
                $courses['offshore_courses'][] = $array;
            }
        }


        return response()->json(['courses' => $courses], 200);
    }

    public function getCourse(Request $request) {

        $carted_or_enrolled = $request->carted_or_enrolled;

        $student = new Student(auth()->user()->makeVisible('id')->toArray());

        $course = match ($request->category) {
            'certificate_course' => CertificateCourse::where('code', $request->identity)->first(),
            'certification_course' => CertificationCourse::where('code', $request->identity)->first(),
            'offshore_course' => OffshoreCourse::where('title', $request->identity)->first(),
        };

        if ($carted_or_enrolled == 'carted') {
            $student->load([
                'carts' => function (Builder $query) use ($course) {
                    $query->where([['course_type',$course::class], ['course_id',$course->id]]);
                }
            ]);

            $cart = $student->carts[0];

            $carted_course = $cart->course;


            if ($carted_course) return response()->json(['status'=>'success', 'course'=> $carted_course], 200);
            else return response()->json(['status'=>'failed', 'message'=>'Couldn\'t fetch course.'], 200);

        } else if ($carted_or_enrolled == 'enrolled') {
            $course = null;

            if ($request->enrollment_type == 'cohort') {
                $cohort = $student->cohorts()->where('name', $request->identity)->first();
                
                $course = $cohort->course;

                $cert = $student->certificates()->where(['type_id'=>1, 'cohort_id'=>$cohort->id])->first();

                $certificate = $cert ? ['name'=>$cohort->name, 'url'=>$cert->url] : null;
            } else if ($request->enrollment_type == 'individual') {
                // $course = match ($request->category) {
                //     'certificate_course' => CertificateCourse::where('code', $request->identity)->first(),
                //     'certification_course' => CertificationCourse::where('code', $request->identity)->first(),
                //     'offshore_course' => OffshoreCourse::where('title', $request->identity)->first(),
                // };

                $cert = $student->certificates()->where(['type_id'=>2, 'course_type'=>get_class($course), 'course_id'=>$course->id])->first();

                if ($request->category == 'offshore_course') $name = $course->title;
                else $name = $course->title.' '.$course->code;


                $certificate = $cert ? ['name'=>$name, 'url'=>$cert->url] : null;

            }

            if (!$course) return response()->json(['status'=>'failed', 'message'=>'Couldn\'t fetch course.'], 200);


            return response()->json(['status'=>'success', 'course'=> $course, 'certificate'=>$certificate], 200);

        }

    }


    public function addCourseToCart(Request $request) {
        $category = $request->category;
        if ($category == 'certificate-course') {
            $course = CertificateCourse::where('code', $request->course_code)->first();
        } else if ($category == 'certification-course') {
            $course = CertificationCourse::where('code', $request->course_code)->first();
        } else if ($category == 'offshore-course') {
            $course = OffshoreCourse::where('title', $request->course_title)->first();
        }

        $student = new Student(auth()->user()->makeVisible('id')->toArray());

        if (!Cart::add($student, $course)) return response()->json(['status'=>'failed', 'message'=>'Could not cart course'], 200);

        return response()->json(['status'=>'success', 'message'=>'Course carted', 'cart'=>base64_encode(json_encode($student->cartedCoursesTitles()))], 200);
    }


    public function addEventToWatchlist(Request $request) {
        $event = Event::where('name', $request->event_name)->first();
        $student = new Student(auth()->user()->makeVisible('id')->toArray());


        try {
            $student->events()->attach($event->id);
            // $student->refresh();
            return response()->json(['status'=>'success', 'message'=>'Event added to watchlist.', 'watchlist'=>base64_encode(json_encode($student->events()->pluck('name')->toArray()))], 200,);
        } catch (\Exception $th) {
            return response()->json(['status'=>'failed', 'message'=>'Something went wrong. Please try again'], 200,);
        }


    }

    public function getEventWatchlist() {

        $student = new Student(auth()->user()->makeVisible('id')->toArray());
        

        $watchlistWithPivot = $student->events()->select('name', 'type', 'image_url')->get()->toArray();


        $watchlist = array_map(function($item) {return ['name' => $item['name'], 'type'=>$item['type'], 'image_url'=>$item['image_url']];}, $watchlistWithPivot);

        if (count($watchlist) == 0) return response()->json(['status'=>'empty', 'message'=>'Your watchlist is empty.'], 200);

       


        return response()->json(['watchlist'=>$watchlist], 200);

    }

    public function getEventFromWatchlist(Request $request, string $event) {
        $student = auth()->user();

        if (!$event = $student->events()->where('name', $event)->first()) return response()->json(['status'=>'failed', 'message'=>'No such event'], 200, $headers);

        return response()->json(['status'=>'success', 'event'=>$event], 200,);
    }


    public function getMyCertificates() {
        $student = new Student(auth()->user()->makeVisible('id')->toArray());


        $certificates = $student->certificates;


        if ($certificates->isEmpty()) return response()->json(['status'=>'empty', 'message'=>'You don\'t have any certificates yet'], 200);

        $data = [];

        foreach ($certificates as $certificate) {
            if ($certificate->type_id == 1) $name = $certificate->cohort->name;
            else {
                if ($certificate->course_type == "App\Models\OffshoreCourse") $name = $certificate->course->title;
                else $name = $name = $certificate->course->title.' - '.$certificate->course->code;
            }

            $url = $certificate->url;

            $data[] = ['name'=>$name, 'url'=>$url];
        }
        
        return response()->json(['status'=>'success', 'certificates'=>$data], 200);
    }


    public function fetch_name(Request $request, string $email) {
        $student = null;
        if (!$student = User::areStudents()->where('email', $email)->first()) return response()->json(['status'=> 'failed', 'message'=> 'student with provided email not found'], 200);

        return response()->json(['status'=>'success', 'message'=>'found', 'name'=>$student->name], 200);
    }

    public function get_all() {
        return response()->json(['students'=>User::areStudents()->select(['first_name', 'last_name', 'email'])->get()], 200);
    }

    public function get(Request $request, string $email) {
        $student = Student::where('email', $email)->first();

        if (!$student) return response()->json(['status'=> 'failed', 'message'=> 'Student with provided email not found'], 200);

        return response()->json(['status'=>'success', 'student'=>$student], 200,);
    }


}
