<?php

use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Citizen\IncidentReportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('citizen.homepage');
});

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware('role:admin')->group(function () {
        Route::get('/register', [RegisteredUserController::class, 'create'])
            ->name('register');
        Route::post('/register', [RegisteredUserController::class, 'store'])
            ->name('register.store');
    });
});

//Citizen workflow routes
Route::prefix('citizen')
    ->middleware('setLocale')
    ->group(function () {

        // Citizen workflow routes
        Route::get('/report', [IncidentReportController::class, 'create'])
            ->name('citizen.report.create');

        Route::post('/report', [IncidentReportController::class, 'store'])
            ->name('citizen.report.store');

        Route::get('/track', [IncidentReportController::class, 'trackForm'])
            ->name('citizen.track.form');

        Route::post('/track', [IncidentReportController::class, 'trackResult'])
            ->name('citizen.track.search');

        Route::get('/received/{ticket}', [IncidentReportController::class, 'received'])
            ->name('citizen.report.received');

        // Citizen locale (manual switch)
        Route::get('/lang/{locale}', function (string $locale) {
            if (! in_array($locale, ['en', 'el'])) {
                abort(404);
            }

            // Persist choice for citizen pages
            session(['locale' => $locale]);

            return back();
        })->name('citizen.lang');
    });

route::view("/offline",'offline')
    ->name('offline');

require __DIR__.'/auth.php';
