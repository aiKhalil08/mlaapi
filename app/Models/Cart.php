<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\DB;

class Cart extends Model
{
    use HasFactory;

    public $timestamps = false;
    public $guarded = ['id'];
    protected $hidden = ['id'];

    public static function add(Student $student, Course $course) {
        try {
            DB::transaction(function () use ($student, $course) {
                DB::table('carts')->insert([
                    'student_id' => $student->id,
                    'course_id' => $course->id,
                    'course_type' => get_class($course),
                ]);
            });
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get the student that owns the Cart
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
    
    public function course(): MorphTo
    {
        return $this->morphTo();
    }
}
