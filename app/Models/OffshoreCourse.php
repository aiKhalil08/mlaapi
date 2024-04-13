<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Course;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class OffshoreCourse extends Course
{

    public function enrollments(): MorphMany
    {
        return $this->morphMany(Sale::class, 'course');
    }
}
