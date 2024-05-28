<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Observers\EventObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;

#[ObservedBy([EventObserver::class])]
class Event extends Model
{
    use HasFactory;

    public $timestamps = false;
    public $guarded = ['id'];
    protected $hidden = ['id'];

    public function getImagePathAttribute() {
        return explode('storage/', $this->image_url)[1];
    }

    public function getImageUrlAttribute(string | null $string) {
        $urls = json_decode($string);
        if (!$string) return null;
        return Storage::url($urls[0]);
    }

    /**
     * The students that belong to the Event
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'event_watchlist');
    }

    /**
     * Get all of the history for the OffshoreCourse
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function history(): HasMany
    {
        return $this->hasMany(EventHistory::class, 'parent_id');
    }

    public function name(): Attribute {
        return Attribute::make(
            set: function($value) {
                if (\substr($value, -1) === '.') {
                    return \substr($value, 0, -1);
                }
                return $value;
            },
        );
    }


    public function attendees(): Attribute {
        return Attribute::make(
            get: fn ($value) => json_decode($value),
            set: fn ($value) => $value ? json_encode($value) : json_encode([]),
        );
    }

    public function price(): Attribute {
        return Attribute::make(
            get: fn ($value) => json_decode($value),
            set: fn ($value) => $value ? json_encode($value) : json_encode([]),
        );
    }

    public function date(): Attribute {
        return Attribute::make(
            get: fn ($value) => json_decode($value),
            set: fn ($value) => $value ? json_encode($value) : json_encode([]),
        );
    }

    public function imageUrls(): Attribute {
        return Attribute::make(
            get: function ($value) {
                $urls = json_decode($value);
                $urls = \array_map(fn ($url) => Storage::url($url), $urls);
                return $urls;
            },
            set: fn ($value) => json_encode($value),
        );
    }

    /**
     * Get all of the registrations for the Event
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function registrations(): HasMany {
        return $this->hasMany(EventRegistration::class);
    }
}
