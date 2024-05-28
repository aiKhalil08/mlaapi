<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserInfo extends Model
{
    use HasFactory;

    protected $table = 'users_info';

    public $timestamps = false;
    public $guarded = [];
    protected $hidden = ['user_id'];
}