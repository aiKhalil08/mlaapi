<?php

namespace App\Observers;

use App\Models\Course;
use App\Models\CertificateCourseHistory;
use App\Models\CertificationCourseHistory;
use App\Models\OffshoreCourseHistory;
use App\Models\AuditTrail;
use App\Enums\ModelEvents;
use Illuminate\Support\Facades\DB;

class CourseObserver
{
    /**
     * Handle the Course "created" event.
     */
    public function created(Course $course): void
    {
        /* record created event in audit trails table */
        $action = ModelEvents::Created;
        $user = auth()->user();
        $object = [];
        
        $object['name'] = match (get_class($course)) {
            'App\Models\CertificateCourse' => 'Certificate Course',
            'App\Models\CertificationCourse' => 'Certification Course',
            'App\Models\OffshoreCourse' => 'Offshore Course',
        };
        $object['model'] = get_class($course);
        $object['id'] = $course->id;

        $attributes = [
            'action' => $action,
            'user_id' => $user->id,
            'object' => json_encode($object)
        ];

        AuditTrail::create($attributes);
    }

    /**
     * Handle the Course "updated" event.
     */
    public function updated(Course $course): void
    {
        DB::transaction(function () use ($course) {
            /* insert old record in history table */
            $history = match (get_class($course)) {
                'App\Models\CertificateCourse' => $course->history()->save(new CertificateCourseHistory([...$course->getOriginal(), 'user_id'=>auth()->id(), 'image_url'=>$course->image_path])),
                'App\Models\CertificationCourse' => $course->history()->save(new CertificationCourseHistory([...$course->getOriginal(), 'user_id'=>auth()->id()])),
                'App\Models\OffshoreCourse' => $course->history()->save(new OffshoreCourseHistory([...$course->getOriginal(), 'user_id'=>auth()->id()])),
            };

            /* record updated event in audit trails table */
            $action = ModelEvents::Updated;
            $user = auth()->user();
            $object = [];
            
            $object['name'] = match (get_class($course)) {
                'App\Models\CertificateCourse' => 'Certificate Course',
                'App\Models\CertificationCourse' => 'Certification Course',
                'App\Models\OffshoreCourse' => 'Offshore Course',
            };
            $object['from']['model'] = get_class($history);
            $object['from']['id'] = $history->id;
            $object['to']['model'] = get_class($course);
            $object['to']['id'] = $course->id;

            $attributes = [
                'action' => $action,
                'user_id' => $user->id,
                'object' => json_encode($object)
            ];

            AuditTrail::create($attributes);
        });
    }

    /**
     * Handle the Course "deleting" event.
     */
    public function deleting(Course $course): void
    {
        DB::transaction(function () use ($course) {
            /* delete existing course history and insert last state of record in history table */
            $course->history()->delete();

            AuditTrail::where([['object->model', $course::class], ['object->id', $course->id]])->orWhere([['object->to->model', $course::class], ['object->to->id', $course->id]])->delete();
            
            $history = match (get_class($course)) {
                'App\Models\CertificateCourse' => CertificateCourseHistory::create([...$course->getOriginal(), 'user_id'=>auth()->id()]),
                'App\Models\CertificationCourse' => CertificationCourseHistory::create([...$course->getOriginal(), 'user_id'=>auth()->id()]),
                'App\Models\OffshoreCourse' => OffshoreCourseHistory::create([...$course->getOriginal(), 'user_id'=>auth()->id()]),
            };

            /* record deleted event in audit trails table */
            $action = ModelEvents::Deleted;
            $user = auth()->user();
            $object = [];
            
            $object['name'] = match (get_class($course)) {
                'App\Models\CertificateCourse' => 'Certificate Course',
                'App\Models\CertificationCourse' => 'Certification Course',
                'App\Models\OffshoreCourse' => 'Offshore Course',
            };
            $object['model'] = get_class($history);
            $object['id'] = $history->id;

            $attributes = [
                'action' => $action,
                'user_id' => $user->id,
                'object' => json_encode($object)
            ];

            AuditTrail::create($attributes);
        });
    }

    /**
     * Handle the Course "restored" event.
     */
    public function restored(Course $course): void
    {
        //
    }

    /**
     * Handle the Course "force deleted" event.
     */
    public function forceDeleted(Course $course): void
    {
        //
    }
}
