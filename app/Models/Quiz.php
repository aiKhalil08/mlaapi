<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;

class Quiz extends Model
{
    use HasFactory;

    public $timestamps = false;
    public $guarded = ['id'];
    protected $hidden = ['id', 'pivot'];

    /**
     * Get all of the questions for the Quiz
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }


    /**
     * The assignedStudents that belong to the Quiz
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function assignedStudents(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'assignments', 'quiz_id', 'user_id');
    }


    /**
     * The assignedStudents that belong to the Quiz
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function assignedExternalUsers(): BelongsToMany
    {
        return $this
        ->belongsToMany(ExternalUser::class, 'assignments', 'quiz_id', 'user_id')
        ->whereHas('roles', function (Builder $query) {
            $query->where('name', 'external_user');
        });
    }


}
