<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Question extends Model
{
    use HasFactory;

    public $timestamps = false;
    public $guarded = ['id'];
    protected $hidden = ['quiz_id', 'type_id'];
    protected $table = 'quiz_questions';


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
     * Get the type associated with the Question
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(QuestionType::class);
    }
}
