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
        $updates = [
        'status' => match ($workOrder->status) {
            'assigned' => 'assigned',
            'in_progress' => 'in_progress',
            'done' => 'resolved',
            'cancelled' => 'cancelled',
            default => $incident->status,
            },
        ];
        // Set closed_at when ticked is closed
        if ($workOrder->status === 'done' && $incident->closed_at === null) {
            $updates['closed_at'] = now();
        }
        // Dont update if nothing changed
        if ($incident->status === $updates['status'] && !isset($updates['closed_at'])) {
            return;
        }

        $incident->update($updates);
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
