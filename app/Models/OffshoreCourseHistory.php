<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OffshoreCourseHistory extends History
{
    use HasFactory;

    protected $table = 'offshore_courses_history';
}
