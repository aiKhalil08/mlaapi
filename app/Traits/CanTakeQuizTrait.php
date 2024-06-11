<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Assignment;
use App\Models\AssignmentUser;
use App\Models\Quiz;
use App\Models\AssignmentSession;


trait CanTakeQuizTrait {

    /**
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function assignments(): BelongsToMany
    {
        return $this
        ->belongsToMany(Assignment::class, 'assignment_user', 'user_id')
        ->wherePivot('done', 0)
        ->using(AssignmentSession::class)
        ->withPivot('id', 'done', 'start_date', 'end_date', 'score');
    }

    public function assignmentsHistory(): BelongsToMany {
        return $this
        ->belongsToMany(Assignment::class, 'assignment_user', 'user_id')
        ->wherePivot('done', 1)
        ->using(AssignmentSession::class)
        ->withPivot('id', 'done', 'start_date', 'end_date', 'score');
    }

}
