<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermissionHistory extends History
{
    use HasFactory;

    protected $table = 'permissions_history';
}
