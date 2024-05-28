<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class EventHistory extends History
{
    use HasFactory;

    protected $table = 'events_history';

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
}
