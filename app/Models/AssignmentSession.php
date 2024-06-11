<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssignmentSession extends Pivot
{
    use HasFactory;
    // protected $table = 'assignment_user';

    /**
     * Get all of the responses for the AssignmentSession
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function responses(): HasMany
    {
        return $this->hasMany(Response::class, 'session_id');
    }
}
