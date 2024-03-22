<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class RefreshToken extends Model
{

    public $timestamps = false;
    public $guarded = ['id'];
    use HasFactory;


    public function tokenable(): MorphTo {
        return $this->morphTo();
    }
}
