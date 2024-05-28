<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Option extends Model
{
    use HasFactory;

    public $timestamps = false;
    public $guarded = ['id'];
    protected $hidden = ['id', 'question_id'];
    protected $table = 'question_options';

    /**
     * Get the question that owns the Option
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    // public function isCorrect(): Attribute
    // {
    //     return Attribute::make(
    //         get: fn ($value) => $value,
    //         set: fn ($value) => $value,
    //     );
    // }
}
