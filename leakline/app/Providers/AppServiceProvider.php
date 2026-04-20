<?php

namespace App\Providers;

use App\Models\Incident;
use App\Models\WorkOrder;
use App\Observers\IncidentObserver;
use App\Observers\WorkOrderObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //add  incident observer and workOrder observer
        Incident::observe(IncidentObserver::class);
        WorkOrder::observe(WorkOrderObserver::class);
    }
}
