<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\Company;
use App\Models\UnhashedPassword;


trait ExternalUserTrait {

    /**
     * The company that belong to the Admin
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'company_external_user', 'user_id');
    }

    public function getCompanyAttribute(): Company {
        return $this->companies[0];
    }

    public function unhashedPassword(): HasOne {
        return $this->hasOne(UnhashedPassword::class, 'user_id');
    }
}
