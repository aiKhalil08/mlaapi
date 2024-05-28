<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Referral extends Model
{
    use HasFactory;

    public $timestamps = false;
    public $guarded = ['id'];
    protected $hidden = ['id'];


    // /**
    //  * Get the buyer that owns the Referral
    //  *
    //  * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
    //  */
    // public function buyer(): BelongsTo
    // {
    //     return $this->belongsTo(User::class);
    // }


    /**
     * Get the referral code for the referral
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function code(): BelongsTo
    {
        return $this->belongsTo(ReferralCode::class);
    }

    /**
     * Get the referral code owner
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOneThrough
     */
    public function referrer(): HasOneThrough
    {
        // return $this->through('code')->has('owner');
        return $this->hasOneThrough(
            User::class,
            ReferralCode::class,
            'id',
            'id',
            'code_id',
            'user_id',
        );
    }

    /* retrieves the course */
    public function course(): MorphTo {
        return $this->morphTo();
    }
    
}
