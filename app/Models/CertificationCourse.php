<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Course;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Observers\CourseObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy([CourseObserver::class])]
class CertificationCourse extends Course
{

    public function enrollments(): MorphMany
    {
        return $this->morphMany(Sale::class, 'course');
    }

    /**
     * Get all of the history for the CertificationCourse
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function history(): HasMany
    {
        return $this->hasMany(CertificationCourseHistory::class, 'parent_id');
    }
}
