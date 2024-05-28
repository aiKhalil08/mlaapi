<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Observers\TestimonialObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy([TestimonialObserver::class])]
class Testimonial extends Resource
{
    // use HasFactory;

    // public function getImageUrlAttribute(string | null $string) {
    //     if (!$string) return null;
    //     return Storage::url($string);
    //     // return request()->schemeAndHttpHost().'/storage/'.$string;
    // }

    /**
     * Get all of the history for the CertificationCourse
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function history(): HasMany
    {
        return $this->hasMany(TestimonialHistory::class, 'parent_id');
    }
}
