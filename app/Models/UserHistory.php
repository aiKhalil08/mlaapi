<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserHistory extends History
{
    use HasFactory;

    protected $table = 'users_history';
}
