<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

interface AdminInterface {

    public function privileges(): BelongsToMany;
}