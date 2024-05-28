<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Cohort extends Model
{
    use HasFactory;

    public $timestamps = false;
    public $guarded = ['id'];
    protected $hidden = ['id', 'status_id'];



    /**
     * Get all of the sales for the Cohort
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function sales(): HasMany {
        return $this->hasMany(Sale::class);
    }

    public function course(): MorphTo {
        return $this->morphTo();
    }
    
    /**
     * The students that belong to the Cohort
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'cohort_user', 'cohort_id', 'user_id');
    }

    /**
     * Get the status that owns the Cohort
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(CohortStatus::class);
    }


    /**
     * Get all of the certificates for the Student
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

}
