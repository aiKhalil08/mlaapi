<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;

class AuditTrail extends Model
{
    use HasFactory;

    public $timestamps = false;
    public $guarded = ['id'];
    // protected $hidden = ['id'];

    /**
     * Get the actor that owns the AuditTrail
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function objectName(): Attribute {
        return Attribute::make(
            get: fn () => json_decode($this->object)->name
        );
    }

    // gets the former version of a record in an update trail
    public function objectFrom(): Attribute {
        // if ($this->action != 'updated') return null;
        return Attribute::make(
            get: function () {
                $model = json_decode($this->object)->from;
                $model_name = $model->model;
                $model_id = $model->id;

                $object = match($model_name) {
                    'App\Models\CertificateCourseHistory' => CertificateCourseHistory::find($model_id),
                    'App\Models\OffshoreCourseHistory' => OffshoreCourseHistory::find($model_id),
                    'App\Models\CertificationCourseHistory' => CertificationCourseHistory::find($model_id),
                };

                return $object;
            }
        );
    }

    // gets the latter version of a record in an update trail
    public function objectTo(): Attribute {
        // if ($this->action != 'updated') return null;
        return Attribute::make(
            get: function () {
                return $this->object_from->next_sibling;
            }
        );
    }


    // gets the record involved in a create or delete trail
    public function objectRecord(): Attribute {
        // if ($this->action == 'updated') return null;
        return Attribute::make(
            get: function () {
                $object = json_decode($this->object);
                $model_name = $object->model;
                $model_id = $object->id;

                $object = match($model_name) {
                    'App\Models\CertificateCourse' => CertificateCourse::find($model_id),
                    'App\Models\OffshoreCourse' => OffshoreCourse::find($model_id),
                    'App\Models\CertificationCourse' => CertificationCourse::find($model_id),
                    'App\Models\CertificateCourseHistory' => CertificateCourseHistory::find($model_id),
                    'App\Models\OffshoreCourseHistory' => OffshoreCourseHistory::find($model_id),
                    'App\Models\CertificationCourseHistory' => CertificationCourseHistory::find($model_id),
                };

                return $object;
            }
        );
    }

}
