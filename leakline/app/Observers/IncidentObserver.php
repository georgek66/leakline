<?php

namespace App\Observers;

use App\Models\Incident;
use App\Models\IncidentEvent;

class IncidentObserver
{
    /**
     * Handle the Incident "created" event.
     */
    public function created(Incident $incident): void
    {
        IncidentEvent::create([
            'incident_id' => $incident->id,
            'actor_id' => auth()->id() ?? null,
            'event_type' => 'created',
            'message' => 'Incident reported and created',
            'meta' => [],
            'created_at' => now(),
        ]);
    }

    /**
     * Handle the Incident "updated" event.
     */
    public function updated(Incident $incident): void
    {
        // Status changed
        if ($incident->wasChanged('status')) {
            IncidentEvent::create([
                'incident_id' => $incident->id,
                'actor_id'    => auth()->id(),
                'event_type'  => 'status_changed',
                'message'     => 'Status changed from ' . $incident->getOriginal('status') . ' to ' . $incident->status . '.',
                'meta'        => [
                    'from' => $incident->getOriginal('status'),
                    'to'   => $incident->status,
                ],
                'created_at'  => now(),
            ]);
        }


    }

    /**
     * Handle the Incident "deleted" event.
     */
    public function deleted(Incident $incident): void
    {
        //
    }

    /**
     * Handle the Incident "restored" event.
     */
    public function restored(Incident $incident): void
    {
        //
    }

    /**
     * Handle the Incident "force deleted" event.
     */
    public function forceDeleted(Incident $incident): void
    {
        //
    }
}
