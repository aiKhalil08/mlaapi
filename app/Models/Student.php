<?php

namespace App\Models;

use App\Traits\CanReceiveOTPTrait;
use App\Contracts\StudentInterface;
use App\Traits\StudentTrait;


class Student extends User implements StudentInterface
{
    use StudentTrait;

    public $guarded = [];

}
