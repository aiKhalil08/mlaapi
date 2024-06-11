<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

interface CanTakeQuizInterface {

    public function assignments(): BelongsToMany;

    public function assignmentsHistory(): BelongsToMany;
}