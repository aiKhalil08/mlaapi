<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoleHistory extends History
{
    use HasFactory;

    protected $table = 'roles_history';
}
