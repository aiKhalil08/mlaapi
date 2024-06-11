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

    public function getAll() { #gets all quizzes for admin
        $quizzes = Quiz::select(['title', 'date_created'])->get();

        return response()->json(['quizzes' => $quizzes], 200);
    }

    public function get(string $title) {
        $quiz = Quiz::where('title', $title)->first();

        if (!$quiz) return response()->json(['status'=>'failed', 'message'=>'No quiz with such title.'], 200);

        $quiz->loadCount('questions');
        $quiz->loadSum('questions as points_sum', 'points');
        $quiz->loadCount('assignments as assignments_count');

        return response()->json(['quiz' => $quiz], 200);
    }

    // public function getAssignments(string $title) {
    //     $quiz = Quiz::where('title', $title)->first();

    //     if (!$quiz) return response()->json(['status'=>'failed', 'message'=>'No quiz with such title.'], 200);

    //     $assignments = $quiz->assignedExternalUsers;

    //     $assignments->load('companies');

    //     $assignments = $assignments->map(function ($student) {
    //         return [...$student->only(['first_name', 'last_name', 'email', 'id']), 'company'=>$student->company];
    //     });

    //     return response()->json(['assignments' => $assignments], 200);
    // }

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
