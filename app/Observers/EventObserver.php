<?php

namespace App\Observers;

use App\Models\Event;
use App\Models\EventHistory;
use App\Models\AuditTrail;
use App\Enums\ModelEvents;
use Illuminate\Support\Facades\DB;

class EventObserver
{
    /**
     * Handle the Event "created" event.
     */
    public function created(Event $event): void
    {
        /* record "created" event in audit trails table */
        $action = ModelEvents::Created;
        $user = auth()->user();
        $object = [];
        
        $object['name'] = 'Event';
        $object['model'] = get_class($event);
        $object['id'] = $event->id;

        $attributes = [
            'action' => $action,
            'user_id' => $user->id,
            'object' => json_encode($object)
        ];

        AuditTrail::create($attributes);
    }

    /**
     * Handle the Event "updated" event.
     */
    public function updated(Event $event): void
    {
        DB::transaction(function () use ($event) {
            /* insert old record in history table */
            $history = $event->history()->save(new EventHistory([...$event->getOriginal(), 'user_id'=>auth()->id()]));

            /* record "updated" event in audit trails table */
            $action = ModelEvents::Updated;
            $user = auth()->user();
            $object = [];
            
            $object['name'] = 'Event';
            $object['from']['model'] = get_class($history);
            $object['from']['id'] = $history->id;
            $object['to']['model'] = get_class($event);
            $object['to']['id'] = $event->id;

            $attributes = [
                'action' => $action,
                'user_id' => $user->id,
                'object' => json_encode($object)
            ];

            AuditTrail::create($attributes);
        });
    }

    /**
     * Handle the Event "deleted" event.
     */
    public function deleting(Event $event): void
    {
        DB::transaction(function () use ($event) {
            /* delete existing event history and insert last state of record in history table */
            $event->history()->delete();
            $history = EventHistory::create([...$event->getOriginal(), 'user_id'=>auth()->id()]);

            /* record deleted event in audit trails table */
            $action = ModelEvents::Deleted;
            $user = auth()->user();
            $object = [];
            
            $object['name'] = 'Event';
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
     * Handle the Event "restored" event.
     */
    public function restored(Event $event): void
    {
        //
    }

    /**
     * Handle the Event "force deleted" event.
     */
    public function forceDeleted(Event $event): void
    {
        //
    }
}
