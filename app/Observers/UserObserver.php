<?php

namespace App\Observers;

use App\Models\User;
use App\Enums\ModelEvents;
use App\Models\UserHistory;
use App\Models\AuditTrail;
use Illuminate\Support\Facades\DB;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        /* record created event in audit trails table */
        $action = ModelEvents::Created;
        $object = [];
        
        $object['name'] = 'User';
        $object['model'] = get_class($user);
        $object['id'] = $user->id;

        $attributes = [
            'action' => $action,
            'user_id' => $user->id,
            'object' => json_encode($object)
        ];

        AuditTrail::create($attributes);
    }

    /**
     * Handle the User "updated" event.
     */
    // public function updated(User $user): void
    // {
    //     DB::transaction(function () use ($user) {
    //         /* insert old record in history table */

    //         $history = $user->history()->save(new UserHistory([...$user->getOriginal()]));

    //         /* record updated event in audit trails table */
    //         $action = ModelEvents::Updated;
    //         $user = auth()->user();
    //         $object = [];
            
    //         $object['name'] = 'User';
    //         $object['from']['model'] = get_class($history);
    //         $object['from']['id'] = $history->id;
    //         $object['to']['model'] = get_class($user);
    //         $object['to']['id'] = $user->id;

    //         $attributes = [
    //             'action' => $action,
    //             'user_id' => $user->id,
    //             'object' => json_encode($object)
    //         ];

    //         AuditTrail::create($attributes);
    //     });
    // }

    /**
     * Handle the User "deleted" event.
     */
    public function deleting(User $user): void
    {
        DB::transaction(function () use ($user) {
            /* delete existing user history and insert last state of record in history table */
            $user->history()->delete();
            $data = [...$user->getOriginal()];
            if (!$user->hasRole('external_user')) $data = [$data, ...$user->info->toArray()];
            $history = UserHistory::create($data);

            /* record deleted event in audit trails table */
            $action = ModelEvents::Deleted;
            $user = auth()->user();
            $object = [];
            
            $object['name'] = 'User';
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
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }
}
