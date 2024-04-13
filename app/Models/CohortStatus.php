<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CohortStatus extends Model
{
    use HasFactory;

    public $timestamps = false;

    /**
     * Get all of the cohort for the CohortStatus
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function cohort(): HasMany
    {
        return $this->hasMany(Cohort::class, 'status_id');
    }
}
