<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Referral extends Model
{
    use HasFactory;

    public $timestamps = false;
    public $guarded = ['id'];
    protected $hidden = ['id'];


    /**
     * Get the buyer that owns the Referral
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'buyer_id', 'id');
    }


    /**
     * Get the referrer that owns the Referral
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function referrer(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'referrer_id', 'id');
    }

    /* retrieves the course */
    public function course(): MorphTo {
        return $this->morphTo();
    }
    
}
