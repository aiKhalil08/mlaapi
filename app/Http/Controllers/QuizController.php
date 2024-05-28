<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\QuestionType;
use App\Models\Option;
use App\Models\ExternalUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;

class QuizController extends Controller
{
    public function store(Request $request) {

        try {
            $quiz = null;
            DB::transaction(function () use ($request, &$quiz) {
                $attributes = ['title'=>$request->title, 'description'=>$request->description];
                
                $quiz = Quiz::create($attributes);
            });
            return response()->json(['status'=>'success'], 200);
        } catch (\Throwable $th) {
            return response()->json(['status'=>'failed', 'message'=>'Something went wrong. Please try again.'], 200);
        }
    }

    public function update(Request $request, string $title) {
        $quiz = Quiz::where('title', $title)->first();

        if (!$quiz) return response()->json(['status'=>'failed', 'message'=>'No quiz with such title.'], 200);

        try {
            DB::transaction(function () use ($request, &$quiz) {
                $attributes = ['title'=>$request->title, 'description'=>$request->description];
                
                $quiz->update($attributes);
            });
            return response()->json(['status'=>'success'], 200);
        } catch (\Throwable $th) {
            return response()->json(['status'=>'failed', 'message'=>'Something went wrong. Please try again.'], 200);
        }
    }

    public function getAll() {
        $quizzes = Quiz::select(['title', 'date_created'])->get();

        return response()->json(['quizzes' => $quizzes], 200);
    }

    public function get(string $title) {
        $quiz = Quiz::where('title', $title)->first();

        if (!$quiz) return response()->json(['status'=>'failed', 'message'=>'No quiz with such title.'], 200);

        $quiz->loadCount('questions');
        $quiz->loadSum('questions as points_sum', 'points');
        $quiz->loadCount('assignedExternalUsers as assignments_count');

        return response()->json(['quiz' => $quiz], 200);
    }

    public function getAllStudents(string $title) { // gets all students indicating those that are assigned to specified quiz

        $quiz = Quiz::where('title', $title)->first();

        if (!$quiz) return response()->json(['status'=>'failed', 'message'=>'No quiz with such title.'], 200);

        // $cohort_id = $cohort->id;

        $ids_for_assigned_students = $quiz->assignedExternalUsers()->pluck('users.id')->toArray();

        // var_dump($ids_for_assigned_students); return null;


        $all_students = ExternalUser::areExternalUsers()->select(['id', 'first_name', 'last_name', 'email'])->with('companies')->get()->map(function ($student) use ($ids_for_assigned_students) {
            
            return ['id' => $student->id, 'first_name'=>$student->first_name, 'last_name'=>$student->last_name, 'email'=>$student->email, 'is_assigned'=>in_array($student->id, $ids_for_assigned_students), 'company'=>$student->company];

        });


        return response()->json(['status'=>'success','students'=>$all_students], 200);
    }

    public function getAssignments(string $title) {
        $quiz = Quiz::where('title', $title)->first();

        if (!$quiz) return response()->json(['status'=>'failed', 'message'=>'No quiz with such title.'], 200);

        $assignments = $quiz->assignedExternalUsers;

        $assignments->load('companies');

        $assignments = $assignments->map(function ($student) {
            return [...$student->only(['first_name', 'last_name', 'email', 'id']), 'company'=>$student->company];
        });

        return response()->json(['assignments' => $assignments], 200);
    }

    public function updateAssignments(Request $request, string $title) {
        $quiz = Quiz::where('title', $title)->first();

        if (!$quiz) return response()->json(['status'=>'failed', 'message'=>'No quiz with such title.'], 200);

        try {
            DB::transaction(function () use ($request, $quiz) {
                $students = $request->students ? array_keys($request->students) : null;
                $quiz->assignedExternalUsers()->syncWithPivotValues($students, ['assigned_by'=>auth()->id()]);
            });
            return response()->json(['status'=>'success'], 200);
        } catch (\Throwable $th) {
            return response()->json(['status'=>'failed','message'=>'Something went wrong. Please try again'], 200);
        }
    }

    public function notify(Request $request, string $title) {

        $request->notify_all = json_decode($request->notify_all);

        $quiz = Quiz::where('title', $title)->first();

        if (!$quiz) return response()->json(['status'=>'failed', 'message'=>'No quiz with such title.'], 200);

        if ($request->notify_all) {
            $students = $quiz->assignedExternalUsers()->select(['users.id', 'first_name', 'last_name', 'email'])
            ->get();
        } else {
            $students = $quiz->assignedExternalUsers()->whereIn('users.id', json_decode($request->recepient_ids))->select(['users.id', 'first_name', 'last_name', 'email'])
            ->get();
        }

        $students->load('unhashedPassword');

        $students = $students->map(function ($student) {
            return [...$student->only(['first_name', 'last_name', 'email']), 'password'=>$student->unhashedPassword->password];
        });

        // var_dump($students->toArray()); return null;
        

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

    public function delete(Request $request, string $title) {
        $quiz = Quiz::where('title', $title)->first();

        if (!$quiz) return response()->json(['status'=>'failed', 'message'=>'No quiz with such title.'], 200);

        try {
            $quiz->delete();
            return response()->json(['status'=>'success','message'=>'Quiz has been deleted.'], 200);
        } catch (\Throwable $th) {
            return response()->json(['status'=>'failed','message'=>'Something went wrong. Please try again'], 200);
        }
    }

    public function addQuestion(Request $request, string $title) {
        $quiz = Quiz::where('title', $title)->first();

        if (!$quiz) return response()->json(['status'=>'failed', 'message'=>'No quiz with such title.'], 200);

        try {
            DB::transaction(function () use ($request, $quiz) {
                $type = QuestionType::where('name', $request->type)->first();
                $question = $quiz->questions()->create(['text'=>$request->text, 'points'=>(int) $request->points, 'type_id'=>$type->id]);
    
                $options = collect($request->options)->map(function ($option_text, $index) use ($request) {
                    return new Option(['text'=>$option_text, 'is_correct'=>$index == (int) $request->correct_option]);
                });
    
                $question->options()->saveMany($options);
            });
            return response()->json(['status'=>'success','message'=>'Question has been added.'], 200);
        } catch (\Throwable $th) {
            return response()->json(['status'=>'failed','message'=>'Something went wrong. Please try again'], 200);
        }
    }

    public function editQuestion(Request $request, string $title, string $question_id) {
        $quiz = Quiz::where('title', $title)->first();

        if (!$quiz) return response()->json(['status'=>'failed', 'message'=>'No quiz with such title.'], 200);

        try {
            DB::transaction(function () use ($request, $question_id, $quiz) {
                $type = QuestionType::where('name', $request->type)->first();
    
                $question = $quiz->questions()->where('id', (int) $question_id)->first();
    
                $question->update(['text'=>$request->text, 'points'=>(int) $request->points, 'type_id'=>$type->id]);
    
                $question->options()->delete();
    
                $options = collect($request->options)->map(function ($option_text, $index) use ($request) {
                    return new Option(['text'=>$option_text, 'is_correct'=>$index == (int) $request->correct_option]);
                });
    
                $question->options()->saveMany($options);
            });
            return response()->json(['status'=>'success','message'=>'Question has been added.'], 200);
        } catch (\Throwable $th) {
            return response()->json(['status'=>'failed','message'=>'Something went wrong. Please try again'], 200);
        }
    }

    public function deleteQuestion(string $title, string $question_id) {
        $quiz = Quiz::where('title', $title)->first();

        if (!$quiz) return response()->json(['status'=>'failed', 'message'=>'No quiz with such title.'], 200);

        try {
            $quiz->questions()->where('id', (int) $question_id)->delete();

            return response()->json(['status'=>'success','message'=>'Question has been deleted.'], 200);
        } catch (\Throwable $th) {
            return response()->json(['status'=>'failed','message'=>'Something went wrong. Please try again'], 200);
        }
    }

    public function getQuestion(string $title, string $question_id) {
        $quiz = Quiz::where('title', $title)->first();

        if (!$quiz) return response()->json(['status'=>'failed', 'message'=>'No quiz with such title.'], 200);

        try {
            $question = $quiz->questions()->where('id', (int) $question_id)->first();

            $question->load(['type', 'options']);

            return response()->json(['status'=>'success','question'=>$question], 200);
        } catch (\Throwable $th) {
            return response()->json(['status'=>'failed','message'=>'Something went wrong. Please try again'], 200);
        }
    }

    public function getQuestions(string $title) {
        $quiz = Quiz::where('title', $title)->first();

        if (!$quiz) return response()->json(['status'=>'failed', 'message'=>'No quiz with such title.'], 200);

        $quiz->load(['questions' => ['type', 'options']]);

        return response()->json(['status'=>'success', 'questions' => $quiz->questions], 200);
    }

}
