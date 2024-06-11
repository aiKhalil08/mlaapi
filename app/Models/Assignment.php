<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;

class Assignment extends Model
{
    use HasFactory;

    public $timestamps = false;
    public $guarded = ['id'];
    protected $hidden = ['id', 'quiz_id'];
    private $type = 'external';

    protected $appends = ['questions_count', 'points_sum'];

    /**
     * Get the quiz that owns the Assignment
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    /**
     * Get the status that owns the Assignment
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(AssignmentStatus::class);
    }

    public function scopeInProgress(Builder $query) {
        $query->where('status_id', 2); // scopes assignments to those have been started (in progress)
    }

    public function shuffle(): Attribute {
        return Attribute::make(
            get: fn ($value) => json_decode($value),
            set: fn ($value) => $value ? json_encode($value) : json_encode([]),
        );
    }

    public function score(): Attribute {
        return Attribute::make(
            get: fn ($value) => json_decode($value),
            set: fn ($value) => $value ? json_encode($value) : json_encode([]),
        );
    }

    public function quizSnapshot(): Attribute {
        return Attribute::make(
            get: fn ($value) => json_decode($value),
        );
    }

    public function questionsCount(): Attribute {
        return Attribute::make(
            get: fn () => count($this->quiz_snapshot->questions),
        );
    }

    public function pointsSum(): Attribute {
        return Attribute::make(
            get: fn () => array_reduce($this->quiz_snapshot->questions, fn($sum, $question) => $sum + $question->points, 0),
        );
    }

    // "cleans" the questions passed to it. by cleaning, it removes all sensitive properties like the correct_answer_id, points and any other properties you don't want to pass to students taking the assignment
    public static function cleanQuestions(array $questions) {
        return collect($questions)->map(function ($question) {
            return collect($question)->except(['correct_answer_id', 'points']);
        });
    }

    /**
     * The assignedStudents that belong to the Quiz
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function students(): BelongsToMany
    {
        if ($this->type == 'internal') {
            return $this
            ->belongsToMany(Student::class, 'assignment_user', 'assignment_id', 'user_id')
            ->whereHas('roles', function (Builder $query) {
                $query->where('name', 'student');
            });
        } else if ($this->type == 'external') {
            return $this
            ->belongsToMany(ExternalUser::class, 'assignment_user', 'assignment_id', 'user_id')
            ->whereHas('roles', function (Builder $query) {
                $query->where('name', 'external_user');
            });
        }
    }

    /**
     * The assignedStudents that belong to the Quiz
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function studentsThatHaveDone(): BelongsToMany
    {
        if ($this->type == 'internal') {
            return $this
            ->belongsToMany(Student::class, 'assignment_user', 'assignment_id', 'user_id')
            ->whereHas('roles', function (Builder $query) {
                $query->where('name', 'student');
            })->wherePivot('done', 1);
        } else if ($this->type == 'external') {
            return $this
            ->belongsToMany(ExternalUser::class, 'assignment_user', 'assignment_id', 'user_id')
            ->whereHas('roles', function (Builder $query) {
                $query->where('name', 'external_user');
            })->wherePivot('done', 1);
        }
    }
}
