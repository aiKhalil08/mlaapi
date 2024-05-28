<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class EventRegistration extends Model
{
    use HasFactory;

    public $timestamps = false;
    public $guarded = ['id'];
    protected $hidden = ['event_id'];

    public function phoneNumber(): Attribute {
        return Attribute::make(
            set: function($value) {
                if (\preg_match('/^0\d{10}$/', $value)) {
                    // check if phone number contains leading zero and trim it away if true
                    $value = \substr($value, 1);
                }
        
                // append country code to phone number
                return '+234'.$value;
            },
        );
    }
}
