<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use App\Models\ReferralCode;
use App\Models\Course;
use App\Models\User;

interface StudentInterface {

    public function sendWelcomeEmail(): bool;

    public function hasVerifiedEmail(): bool;

    public function studentInfo(): HasOne;

    public function carts(): HasMany;

    public function events(): BelongsToMany;

    public function purchases(): HasMany;

    public function cohorts(): BelongsToMany;

    public function certificates(): HasMany;

    public function cartedCourses(): array;

    public function cartedCoursesTitles(): array;
}