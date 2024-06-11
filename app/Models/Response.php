<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Response extends Model
{
    use HasFactory;

    public $timestamps = false;
    public $guarded = ['id', 'user_id'];
    protected $table = 'quiz_responses';
}
