<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Question extends Model
{
    use HasFactory;

    public $timestamps = false;
    public $guarded = ['id'];
    protected $hidden = ['quiz_id', 'type_id', 'correctAnswer'];
    protected $table = 'quiz_questions';

    protected $appends = ['correct_answer_id'];


    /**
     * Get all of the options for the Question
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function options(): HasMany
    {
        return $this->hasMany(Option::class);
    }

    /**
     * Get the correctAnswer associated with the Question
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function correctAnswer(): HasOne
    {
        return $this->hasOne(Option::class)->where('is_correct', true);
    }

    public function correctAnswerId(): Attribute {
        return Attribute::make(
            get: fn () => $this->correctAnswer->id,
        );
    }

    /**
     * Get the type associated with the Question
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(QuestionType::class);
    }
}
