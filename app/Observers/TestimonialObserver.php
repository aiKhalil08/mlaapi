<?php

namespace App\Observers;

use App\Models\Testimonial;
use App\Models\TestimonialHistory;
use App\Models\AuditTrail;
use App\Enums\ModelEvents;
use Illuminate\Support\Facades\DB;

class TestimonialObserver
{
    /**
     * Handle the Testimonial "created" event.
     */
    public function created(Testimonial $testimonial): void
    {
        /* record created event in audit trails table */
        $action = ModelEvents::Created;
        $user = auth()->user();
        $object = [];
        
        $object['name'] = 'Testimonial';
        $object['model'] = get_class($testimonial);
        $object['id'] = $testimonial->id;

        $attributes = [
            'action' => $action,
            'user_id' => $user->id,
            'object' => json_encode($object)
        ];

        AuditTrail::create($attributes);
    }

    /**
     * Handle the Testimonial "updated" event.
     */
    public function updated(Testimonial $testimonial): void
    {
        DB::transaction(function () use ($testimonial) {
            /* insert old record in history table */
            $history = $testimonial->history()->save(new TestimonialHistory([...$testimonial->getOriginal(), 'user_id'=>auth()->id()]));

            /* record updated event in audit trails table */
            $action = ModelEvents::Updated;
            $user = auth()->user();
            $object = [];
            
            $object['name'] = 'Testimonial';
            $object['from']['model'] = get_class($history);
            $object['from']['id'] = $history->id;
            $object['to']['model'] = get_class($testimonial);
            $object['to']['id'] = $testimonial->id;

            $attributes = [
                'action' => $action,
                'user_id' => $user->id,
                'object' => json_encode($object)
            ];

            AuditTrail::create($attributes);
        });
    }

    /**
     * Handle the Testimonial "deleted" event.
     */
    public function deleting(Testimonial $testimonial): void
    {
        DB::transaction(function () use ($testimonial) {
            /* delete existing testimonial history and insert last state of record in history table */
            $testimonial->history()->delete();
            $history = TestimonialHistory::create([...$testimonial->getOriginal(), 'user_id'=>auth()->id()]);

            /* record deleted event in audit trails table */
            $action = ModelEvents::Deleted;
            $user = auth()->user();
            $object = [];
            
            $object['name'] = 'Testimonial';
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
     * Handle the Testimonial "restored" event.
     */
    public function restored(Testimonial $testimonial): void
    {
        //
    }

    /**
     * Handle the Testimonial "force deleted" event.
     */
    public function forceDeleted(Testimonial $testimonial): void
    {
        //
    }
}
