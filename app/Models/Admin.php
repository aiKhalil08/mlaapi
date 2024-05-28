<?php

namespace App\Models;

use App\Contracts\AdminInterface;
use App\Traits\AdminTrait;


class Admin extends User# implements AdminInterface
{
    #use AdminTrait;
}
