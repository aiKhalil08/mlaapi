<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use App\Models\ReferralCode;
use Illuminate\Database\Eloquent\Builder;

interface CanBeAffiliateInterface {

    public function fulfillments(): HasMany;

    public function referralCodeHasExpired(): bool;

    public function referralCount(): int;

    public function getTotalCommissionAttribute(): int | null;

    public function getCompletedPayoutAttribute(): int | null;

    public function getPendingPayoutAttribute(): int | null;

    public function getWithdrawableAmountAttribute(): int | null;

    public function affiliatePortal(): array;

    public function generateReferralCode(): string;

    public function referralCodes(): HasMany;

    public function referralCode(): HasOne;

    public function getReferralCodeStringAttribute(): string |null;

    public function isAffiliate(): bool;

    public function referrals(): HasManyThrough;

    public function scopeAreAffiliates(Builder $query): void;
}