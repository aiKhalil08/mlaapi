<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\belongsTo;

class History extends Model
{
    use HasFactory;

    public $timestamps = false;
    public $guarded = ['id'];
    protected $hidden = ['id'];

    public function getImagePathAttribute() {
        return explode('storage/', $this->image_url)[1];
    }
    

    public function getImageUrlAttribute(string | null $string) {
        if (!$string) return null;
        return Storage::url($string);
    }

    /**
     * Get the parent associated with the History
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function parent(): belongsTo
    {

        $parent_class = str_replace('History', '', $this::class);
        return $this->belongsTo($parent_class, 'parent_id');
    }


    public function nextSibling(): Attribute {
        return Attribute::make(
            get: function() {

                $object = $this::where([['parent_id', $this->parent_id], ['date_added', '>', $this->date_added]])->take(1)->first();

                return $object ?: $this->parent;
            },
        );
    }

    public function objectives(): Attribute {
        return Attribute::make(
            get: fn ($value) => json_decode($value),
            set: fn ($value) => $value ? json_encode($value) : json_encode([]),
        );
    }

    public function prerequisites(): Attribute {
        return Attribute::make(
            get: fn ($value) => json_decode($value),
            set: fn ($value) => $value ? json_encode($value) : json_encode([]),
        );
    }

    public function attendees(): Attribute {
        return Attribute::make(
            get: fn ($value) => json_decode($value),
            set: fn ($value) => $value ? json_encode($value) : json_encode([]),
        );
    }

    public function modules(): Attribute {
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
}
