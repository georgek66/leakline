<?php

namespace App\Observers;

use App\Models\WorkOrder;

class WorkOrderObserver
{
    /**
     * Handle the WorkOrder "created" event.
     */
    public function created(WorkOrder $workOrder): void
    {
        //
    }

    /**
     * Handle the WorkOrder "updated" event.
     */
    public function updated(WorkOrder $workOrder): void
    {
        if (! $workOrder->wasChanged('status')){
            return;
        }

        $incident = $workOrder->incident;

        if ( ! $incident){
            return;
        }

        $incidentStatus = match($workOrder->status) {
            'assigned' => 'assigned',
            'done'      => 'resolved',
            'cancelled' => 'cancelled',
            'in_progress' => 'in_progress',
            default     => $incident->status,
        };

        if($incident->status === $incidentStatus){
            return;
        }

        $workOrder->incident->update([
            'status' => $incidentStatus
        ]);


    }

    /**
     * Handle the WorkOrder "deleted" event.
     */
    public function deleted(WorkOrder $workOrder): void
    {
        //
    }

    /**
     * Handle the WorkOrder "restored" event.
     */
    public function restored(WorkOrder $workOrder): void
    {
        //
    }

    /**
     * Handle the WorkOrder "force deleted" event.
     */
    public function forceDeleted(WorkOrder $workOrder): void
    {
        //
    }
}
