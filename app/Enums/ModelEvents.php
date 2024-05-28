<?php

namespace App\Enums;


enum ModelEvents: string {
    case Created = 'created';
    case Updated = 'updated';
    case Deleted = 'deleted';
}