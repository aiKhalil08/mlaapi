<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Contracts\ExternalUserInterface;
use App\Contracts\CanTakeQuizInterface;
use App\Traits\ExternalUserTrait;
use App\Traits\CanTakeQuizTrait;

class ExternalUser extends User implements ExternalUserInterface, CanTakeQuizInterface
{
    use HasFactory, ExternalUserTrait, CanTakeQuizTrait;

    public $timestamps = false;
    public $guarded = [];
    protected $hidden = ['id', 'pivot', 'password'];
    protected $table = 'users';
}
