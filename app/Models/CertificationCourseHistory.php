<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CertificationCourseHistory extends History
{
    use HasFactory;

    protected $table = 'certification_courses_history';
}
