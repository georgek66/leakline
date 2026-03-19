<?php

namespace App\Providers;

use App\Models\Incident;
use App\Observers\IncidentObserver;
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
        //add  incident observer
        Incident::observe(IncidentObserver::class);
    }
}
