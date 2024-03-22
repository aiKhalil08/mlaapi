<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Event extends Model
{
    use HasFactory;

    public $timestamps = false;
    public $guarded = ['id'];
    protected $hidden = ['id'];

    public function getActualImageUrlAttribute() {
        return explode('storage/', $this->image_url)[1];
    }

    public function getImageUrlAttribute(string $string) {
        // return 'http://localhost:8000/storage/'.$string;
        return request()->schemeAndHttpHost().'/storage/'.$string;
        // return route('storage/'.$string);
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
}
