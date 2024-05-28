<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnhashedPassword extends Model
{
    use HasFactory;

    public $timestamps = false;
    public $fillable = ['password'];
    protected $hidden = ['id', 'user_id'];
}
