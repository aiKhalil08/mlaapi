<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestimonialHistory extends History
{
    use HasFactory;

    protected $table = 'testimonials_history';
}
