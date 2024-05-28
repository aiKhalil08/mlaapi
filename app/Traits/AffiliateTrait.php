<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use App\Models\ReferralCode;
use App\Models\Fulfillment;
use Illuminate\Support\Facades\DB;
use Sinergi\Token\StringGenerator;
use Illuminate\Database\Eloquent\Builder;

trait AffiliateTrait {
    /**
     * Get all of the fulfillments for the Student
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function fulfillments(): HasMany
    {
        return $this->hasMany(Fulfillment::class);
    }

    public function referralCodeHasExpired(): bool {
        if (!$this->isAffiliate()) return true;
        return \Carbon\Carbon::now() > $this->referralCode->expires_at;
    }

    public function referralCount(): int {
        return $this->referrals->count();
    }


    public function getTotalCommissionAttribute(): int | null {
        return $this->referrals()->sum('referrals.commission');
    }

    public function getCompletedPayoutAttribute(): int | null { // all completed commission withdrawal
        return $this->fulfillments()->where('status_id', 1)->sum('amount');
    }


    public function getPendingPayoutAttribute(): int | null { // pending payout requests
        return $this->fulfillments()->where('status_id', '0')->sum('amount');
    }


    public function getWithdrawableAmountAttribute(): int | null {
        return (int) $this->total_commission - (int) $this->pending_payout - (int) $this->completed_payout;
    }


    public function affiliatePortal(): array {
        return [
            'referral_code' => $this->referralCode->code,
            'is_expired' => $this->referralCodeHasExpired(),
            'total_referrals' => $this->referralCount(),
            'total_commission' => $this->total_commission,
            'completed_payout' => $this->completed_payout,
            'referral_code_commission_percentage' => $this->referralCode->commission,
            'payout_history' => $this->fulfillments()->select('amount', 'type', 'status_id', 'date_added')->with([
                'status:id,name'
            ])->orderByDesc('date_added')->get(),
            'pending_payout' => $this->pending_payout,
            'withdrawable_amount' => $this->withdrawable_amount,
        ];
    }

    public function generateReferralCode(): string {
        return StringGenerator::randomAlnum(8);
    }


    /**
     * Get the referrer_code associated with the Student
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function referralCodes(): HasMany
    {
        return $this->hasMany(ReferralCode::class, 'user_id');
    }


    /**
     * Get the referralCode associated with the AffiliateTrait
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function referralCode(): HasOne
    {
        return $this->hasOne(ReferralCode::class, 'user_id')->ofMany(['created_at'=> 'max'], function ($query) {
            $query->where('validity', 1);
        });
    }

    public function getReferralCodeStringAttribute(): string |null
    {
        return $this->referralCode ? $this->referralCode->code : null;
    }


    public function isAffiliate(): bool {
        return $this->referralCode != null;
    }

    /**
     * Get all of the referrals for the Student
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function referrals(): HasManyThrough
    {
        return $this->through('referralCodes')->has('referrals');
    }


    public function scopeAreAffiliates(Builder $query): void {
        $query->has('referralCode');
    }

}