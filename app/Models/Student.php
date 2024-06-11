<?php

namespace App\Models;

use App\Traits\CanReceiveOTPTrait;
use App\Contracts\StudentInterface;
use App\Traits\StudentTrait;
use App\Traits\CanTakeQuizTrait;


class Student extends User implements StudentInterface
{
    use StudentTrait, CanTakeQuizTrait;

    public $guarded = [];
    protected $table = 'users';

}
