<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

interface ExternalUserInterface {

    public function companies(): BelongsToMany;

    public function getCompanyAttribute(): \App\Models\Company;

    public function unhashedPassword(): HasOne;
}