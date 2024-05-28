<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReferralCode extends Model
{
    use HasFactory;

    public $timestamps = false;
    public $guarded = ['id'];
    protected $hidden = ['id'];

    /**
     * Get the student that owns the ReferralCode
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

     /**
     * Get all of the referrals for the Student
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function referrals(): HasMany
    {
        return $this->hasMany(Referral::class, 'code_id');
    }

}
