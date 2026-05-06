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
        $this->syncIncidentStatus($workOrder);
    }

    /**
     * Handle the WorkOrder "updated" event.
     */
    public function updated(WorkOrder $workOrder): void
    {
        if (!$workOrder->wasChanged('status')) {
            return;
        }
        $this->syncIncidentStatus($workOrder);

    }

    public function syncIncidentStatus(WorkOrder $workOrder): void
    {
        $incident = $workOrder->incident;

        if (!$incident) {
            return;
        }

        $incidentStatus = match ($workOrder->status) {
            'assigned' => 'assigned',
            'in_progress' => 'in_progress',
            'done' => 'resolved',
            'cancelled' => 'cancelled',
            default => $incident->status,
        };

        if ($incident->status === $incidentStatus) {
            return;
        }

        $incident->update([
            'status' => $incidentStatus,
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
