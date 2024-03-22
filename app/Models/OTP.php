<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class OTP extends Model
{
    use HasFactory;

    public $timestamps = false;
    public $guarded = ['id'];
    protected $hidden = ['id'];
    protected $table = 'otps';


    public function owner(): MorphTo {
        return $this->morphTo();
    }

    public function isExpired(): bool {
        return \Carbon\Carbon::now() > $this->expires_at;
    }
}
