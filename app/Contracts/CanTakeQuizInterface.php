<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

interface CanTakeQuizInterface {

    public function pendingAssignments(): BelongsToMany;

    public function doneAssignments(): BelongsToMany;
}