<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;


trait AdminTrait {

    /**
     * The privileges that belong to the Admin
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function privileges(): BelongsToMany
    {
        return $this->belongsToMany(Privilege::class);
    }
}
