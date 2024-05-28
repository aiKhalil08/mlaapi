<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Assignment extends Pivot
{
    use HasFactory;

    public $timestamps = false;
    public $guarded = ['id'];
    protected $hidden = ['id', 'pivot'];

    public $incrementing = true;

    /**
     * Get the status that owns the Assignment
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(AssignmentStatus::class);
    }

    /**
     * Get the quiz that owns the Assignment
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    /**
     * Get the assigner that owns the Assignment
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
