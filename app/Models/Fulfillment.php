<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Fulfillment extends Model
{
    use HasFactory;

    public $timestamps = false;
    public $guarded = ['id'];
    protected $hidden = ['user_id', 'status_id'];


    /**
     * Get the student that owns the Fulfillment
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the status that owns the Cohort
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(FulfillmentStatus::class);
    }

    public function scopePending($query): void {
        $query->where('status_id', 0);
    }

    public function scopeFulfilled($query): void {
        $query->where('status_id', 1);
    }

    public function scopeRejected($query): void {
        $query->where('status_id', 2);
    }

    public function scopeAffiliateNotDeleted(Builder $query) { // scopes to fulfillments where affiliate is not deleted
        $query->has('affiliate');
    }

    public function accountDetails(): Attribute {
        return Attribute::make(
            get: fn ($value) => json_decode($value),
            set: fn ($value) => $value ? json_encode($value) : json_encode([]),
        );
    }
}
