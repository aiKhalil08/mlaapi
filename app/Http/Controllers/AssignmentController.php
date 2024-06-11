<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Quiz;
use App\Models\Assignment;
use App\Models\AssignmentStatus;
use App\Models\ExternalUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Carbon;
use Illuminate\Http\Client\ConnectionException;

class AssignmentController extends Controller
{
    
    public function store(Request $request) {
        $quiz = Quiz::where('title', $request->quiz_title)->first();

        if (!$quiz) return response()->json(['status'=>'failed', 'message'=>'Could not find quiz with specified title.'], 200);

        try {
            // create quiz snapshot by loading all the associated questions and options and converting to json
            $quiz->load(['questions' => ['options:question_id,id,text']]);
            DB::transaction(function () use ($request, $quiz) {
                $quiz->assignments()->create([
                    ...$request->only(['name', 'description', 'shuffle', 'duration']),
                    'created_by'=> auth()->id(),
                    'status_id'=>1,
                    'quiz_snapshot'=>$quiz->toJson(),
                ]);
            });
            return response()->json(['status'=>'success'], 200);
        } catch (\Throwable $th) {
            return response()->json(['status'=>'failed','message'=>'Something went wrong. Please try again'], 200);
        }
    }

    public function edit(Request $request, string $name) {
        $assignment = Assignment::where('name', $name)->with(['quiz:id,title', 'status:id,name'])->first();

        if (!$assignment) return response()->json(['status'=>'failed', 'message'=>'No assignment with such name.'], 200);

        try {
            DB::transaction(function () use ($request, &$assignment) {
                $attributes = [
                    ...$request->only(['name', 'description', 'shuffle', 'duration']),
                ];
                
                $assignment->update($attributes);
            });
            return response()->json(['status'=>'success'], 200);
        } catch (\Throwable $th) {
            return response()->json(['status'=>'failed', 'message'=>'Something went wrong. Please try again.'], 200);
        }
    }

    public function getAll() { #gets all assignments for admin
        $assignments = Assignment::select(['id', 'name', 'quiz_id'])->with('quiz:id,title')->get();

        return response()->json(['assignments' => $assignments->makeHidden(['questions_count', 'points_sum'])], 200);
    }

    public function get(string $name) {
        $assignment = Assignment::where('name', $name)->with(['quiz:id,title', 'status:id,name'])->first();

        if (!$assignment) return response()->json(['status'=>'failed', 'message'=>'No assignment with such name.'], 200);

        $assignment->loadCount('students');
        $assignment->loadCount('studentsThatHaveDone');
        // $assignment->loadCount('assignedExternalUsers as assignments_count');

        return response()->json(['assignment' => $assignment], 200);
    }

    public function delete(Request $request, string $name) {
        $assignment = Assignment::where('name', $name)->with(['quiz:id,title', 'status:id,name'])->first();

        if (!$assignment) return response()->json(['status'=>'failed', 'message'=>'No assignment with such name.'], 200);

        try {
            $assignment->delete();
            return response()->json(['status'=>'success','message'=>'Assignment has been deleted.'], 200);
        } catch (\Throwable $th) {
            return response()->json(['status'=>'failed','message'=>'Something went wrong. Please try again'], 200);
        }
    }

    public function getAllStudents(string $name) { // gets all students indicating those that are assigned to specified assignment

        $assignment = Assignment::where('name', $name)->first();

        if (!$assignment) return response()->json(['status'=>'failed', 'message'=>'No assignment with such name.'], 200);

        $ids_for_assigned_students = $assignment->students()->areExternalUsers()->pluck('users.id')->toArray();

        // var_dump($ids_for_assigned_students); return null;


        $all_students = ExternalUser::areExternalUsers()->select(['id', 'first_name', 'last_name', 'email'])->with('companies')->get()->map(function ($student) use ($ids_for_assigned_students) {
            
            return ['id' => $student->id, 'first_name'=>$student->first_name, 'last_name'=>$student->last_name, 'email'=>$student->email, 'is_assigned'=>in_array($student->id, $ids_for_assigned_students), 'company'=>$student->company];

        });


        return response()->json(['status'=>'success','students'=>$all_students], 200);
    }

    public function updateStudents(Request $request, string $name) {
        $assignment = Assignment::where('name', $name)->first();

        if (!$assignment) return response()->json(['status'=>'failed', 'message'=>'No assignment with such name.'], 200);

        try {
            DB::transaction(function () use ($request, $assignment) {
                $students = $request->students ? array_keys($request->students) : null;
                $assignment->students()->syncWithPivotValues($students, ['assigned_by'=>auth()->id()]);
            });
            return response()->json(['status'=>'success'], 200);
        } catch (\Throwable $th) {
            return response()->json(['status'=>'failed','message'=>'Something went wrong. Please try again'], 200);
        }
    }

    public function notifyStudents(Request $request, string $name) {
        $assignment = Assignment::where('name', $name)->first();

        if (!$assignment) return response()->json(['status'=>'failed', 'message'=>'No assignment with such name.'], 200);

        if ($request->notify_all) {
            $students = $assignment->students()->select(['users.id', 'first_name', 'last_name', 'email'])
            ->get();
        } else {
            $students = $assignment->students()->whereIn('users.id', json_decode($request->recepient_ids))->select(['users.id', 'first_name', 'last_name', 'email'])
            ->get();
        }

        $students->load('unhashedPassword');

        $students = $students->map(function ($student) {
            return [...$student->only(['first_name', 'last_name', 'email']), 'password'=>$student->unhashedPassword->password];
        });

        $template = $request->body;

        $pattern = '/{{(\w+)}}/';

        $matches = [];
        
        if (preg_match_all($pattern, $template, $matches)) {
            
            $acceptable_attributes = ['first_name', 'last_name', 'email', 'password'];
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
            'recipients' => $students->toArray(),
        ];

        $api_endpoint = 'https://mitiget.com.ng/bulkmailer/dynamicbulk.php';

        try {
            $response = Http::asForm()->post($api_endpoint, $data);

            if ($response->ok()) return response()->json(['status'=>'success', 'message'=>'Students have been notified'], 200);
            else return response()->json(['status'=>'failed', 'message'=>'Something went wrong. Please try again.'], 200);
        } catch (\Throwable $th) {
            if ($th instanceof ConnectionException) return response()->json(['status'=>'failed', 'message'=>'Please check your network connection and try again.']);
            return response()->json(['status'=>'failed', 'message'=>'Something went wrong. Please try again.']);
        }
        
    }

    public function getStudents(string $name) {
        $assignment = Assignment::where('name', $name)->first();

        if (!$assignment) return response()->json(['status'=>'failed', 'message'=>'No assignment with such name.'], 200);

        $students = $assignment->students;

        $students->load('companies');

        $students = $students->map(function ($student) {
            return [...$student->only(['first_name', 'last_name', 'email', 'id']), 'company'=>$student->company];
        });

        return response()->json(['students' => $students], 200);
    }

    public function changeStatus(string $name, string $new_status) {
        $assignment = Assignment::where('name', $name)->first();

        if (!$assignment) return response()->json(['status'=>'failed', 'message'=>'No assignment with such name.'], 200);

        $status_names = ['start'=>'In progress', 'conclude'=>'Concluded'];

        $status = AssignmentStatus::where('name', $status_names[$new_status])->first();

        if (!$status) return response()->json(['status'=>'failed', 'message'=>'Something went wrong. Please try again.'], 200);

        $columns = ['start'=>'start_date', 'conclude'=>'end_date'];

        try {
            $date = \Carbon\Carbon::now();
            $assignment->update(['status_id'=>$status->id, $columns[$new_status]=>$date]);
            return response()->json(['status'=>'success', $columns[$new_status]=>$date, 'message'=>'Assignment has been '.$new_status.'ed.'], 200);
        } catch (\Throwable $th) {
            return response()->json(['status'=>'failed','message'=>'Something went wrong. Please try again'], 200);
        }
    }


    // methods pertaining to taking quiz (students and external users)

    public function getPendingAssignments() {
        $user = auth()->user()->castToExternalUser();

        $assignment_ids = $user->assignments()->pluck('assignment_id');
        
        $assignments = Assignment::inProgress()->whereIn('id', $assignment_ids)->select(['name'])->get();

        return response()->json(['assignments'=>$assignments->makeHidden(['questions_count', 'points_sum'])], 200);
    }

    public function getCompletedAssignments() {
        $user = auth()->user()->castToExternalUser();

        $assignments = Assignment::select('name', 'assignment_user.end_date', 'score')
        ->join('assignment_user', 'assignments.id', '=', 'assignment_user.assignment_id')
        ->where('assignment_user.user_id', $user->id)
        ->where('assignment_user.done', 1)
        ->get();

        return response()->json(['assignments'=>$assignments->makeHidden(['questions_count', 'points_sum'])], 200);
    }
    
    public function getAssignment(string $name) {
        $user = auth()->user()->castToExternalUser();

        $assignment = Assignment::inProgress()->where('name', $name)->first();

        if (!$assignment) return response()->json(['status'=>'empty', 'message'=>'Assignment with provided name could not be found'], 200);


        return response()->json(['assignment'=>$assignment->setVisible(['name', 'description', 'duration', 'start_date', 'points_sum', 'questions_count'])], 200);
    }

    public function getAssignmentQuestions(string $name) { // user has starts taking assignment
        $user = auth()->user()->castToExternalUser();

        $assignment = $user->assignments()->where('name', $name)->first();

        if (!$assignment) return response()->json(['status'=>'empty', 'message'=>'Assignment with provided name could not be found'], 200);

        $questions = Assignment::cleanQuestions($assignment->quiz_snapshot->questions); // removes sensitive properties from questions

        // var_dump($assignment->pivot->id); return null;

        $assignment->pivot->update(['start_date'=> Carbon::now()]); // set start time


        return response()->json(['session'=>['questions'=>$questions, 'duration'=>$assignment->duration, 'shuffle'=>$assignment->shuffle]], 200);
    }

    public function getAssignmentReview(string $name) {
        $user = auth()->user()->castToExternalUser();

        $assignment = $user->assignmentsHistory()->where('name', $name)->first();

        if (!$assignment) return response()->json(['status'=>'empty', 'message'=>'Assignment with provided name could not be found'], 200);

        
        $questions = $assignment->quiz_snapshot->questions;
        $responses = $assignment->pivot->responses()->get(['question_id', 'option_id']);


        return response()->json(['questions'=>$questions, 'responses'=>$responses], 200);
    }

    public function submitAssignment(Request $request, string $name) {
        $user = auth()->user()->castToExternalUser();

        $assignment = $user->assignments()->where('name', $name)->first();

        if (!$assignment) return response()->json(['status'=>'failed', 'message'=>'No Assignment with such name.'], 200);

        $question_ids = array_keys($request->responses); // ids of assignment question questions
        $option_ids = $request->responses; // ids of user selected options

        $responses = [];

        $questions = collect($assignment->quiz_snapshot->questions);
        
        $points_obtained = 0;
        $points_obtainable = $assignment->points_sum;
        $correct_answers = 0;

        for ($i = 0; $i < count($question_ids); $i++) {
            $q_id = $question_ids[$i];
            $o_id = $option_ids[$q_id];

            $question = $questions->firstWhere('id', $q_id);
            if ($question->correct_answer_id == $o_id) {
                $points_obtained = $points_obtained + $question->points;
                $correct_answers++;
            }
            $responses[] = ['question_id'=>$q_id, 'option_id'=>$o_id];
        }

        // set end_date and score
        $pivot = $assignment->pivot;
        $pivot->update([
            'end_date'=> \Carbon\Carbon::now(),
            'score'=> ['obtained'=>$points_obtained, 'obtainable'=>$points_obtainable],
            'done'=> 1
        ]);

        $pivot->responses()->createMany($responses);

        $duration = $pivot->end_date->diff(Carbon::parse($pivot->start_date));

        $hours = $duration->h > 0 ? ($duration->h > 1 ? $duration->h.' hours' : $duration->h.' hour') : '';
        $minutes = $duration->i > 0 ? ($duration->i > 1 ? $duration->i.' minutes' : $duration->i.' minute') : '';
        $seconds = $duration->s > 0 ? ($duration->s > 1 ? $duration->s.' seconds' : $duration->s.' second') : '';

        $duration_string = $hours.' '.$minutes.' '.$seconds;

        return response()->json(['status' => 'success', 'attempt_summary'=>[
            'points_obtained'=>$points_obtained,
            'points_obtainable' => $points_obtainable,
            'correct_answers'=>$correct_answers,
            'time_taken'=>trim($duration_string),
        ]], 200);

    }
}
