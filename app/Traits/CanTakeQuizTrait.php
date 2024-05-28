<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Assignment;
use App\Models\Quiz;


trait CanTakeQuizTrait {

    /**
     * The company that belong to the Admin
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function pendingAssignments(): BelongsToMany
    {
        return $this
        ->belongsToMany(Quiz::class, 'assignments', 'user_id')
        ->using(Assignment::class);
    }

    public function doneAssignments(): BelongsToMany {
        return $this
        ->belongsToMany(Quiz::class, 'assignments', 'user_id')
        ->using(Assignment::class);
    }

}
