<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Blog extends Resource
{
    public function heading(): Attribute {
        return Attribute::make(
            set: function($value) {
                if (\substr($value, -1) === '.') {
                    return \substr($value, 0, -1);
                }
                return $value;
            },
        );
    }
    
}
