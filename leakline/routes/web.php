<?php

use App\Http\Controllers\Citizen\IncidentReportController;
use App\Http\Controllers\Coordinator\CoordinatorDashboardController;
use App\Http\Controllers\Technician\TechnicianController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use function Pest\Laravel\get;

Route::get('/', function () {
    return view('citizen.homepage');
});

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

//Citizen workflow routes
Route::prefix('citizen')
    ->middleware('setLocale')
    ->group(function () {


        Route::get('/report', [IncidentReportController::class, 'create'])
            ->name('citizen.report.create');

        Route::post('/report', [IncidentReportController::class, 'store'])
            ->name('citizen.report.store');

        Route::get('/track', [IncidentReportController::class, 'trackForm'])
            ->name('citizen.track.form');
        Route::delete('/contact/{token}', [IncidentReportController::class, 'destroyByToken'])
            ->name('citizen.contact.delete');
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

        Route::view('/privacy', 'legal.privacy')
            ->name('privacy');

    });

// Admin routes
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [AdminUserController::class, 'index'])
            ->name('dashboard');

        Route::resource('users', AdminUserController::class)->only(['index', 'edit', 'update', 'destroy']);
    });

// Coordinator routes
Route::middleware(['auth', 'role:coordinator,admin'])
    ->prefix('coordinator')
    ->name('coordinator.')
    ->group(function () {
        Route::get('/dashboard', [CoordinatorDashboardController::class, 'index'])
            ->name('dashboard');

        Route::post('/incidents/{incident}/merge', [CoordinatorDashboardController::class, 'merge'])
            ->name('incidents.merge');


        Route::get('/incidents/{incident}', [CoordinatorDashboardController::class, 'show'])
            ->name('incidents.show');

        Route::post('/incidents/{incident}/assign-technician', [CoordinatorDashboardController::class, 'assignTechnician'])
            ->name('incidents.assign-technician');

        Route::get('/reports', [CoordinatorDashboardController::class, 'reports'])
            ->name('reports');

        Route::get('/reports/download',[CoordinatorDashboardController::class, 'generate'])
            ->name('reports.download');

//        Route::post('/incidents/{incident}/assign-team', [CoordinatorDashboardController::class, 'assignTechnician'])
//            ->name('incidents.assign-team');

    });

// Technician routes
Route::middleware(['auth', 'role:technician,admin'])
    ->prefix('technician')
    ->name('technician.')
    ->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Technician\TechnicianController::class, 'index'])
            ->name('dashboard');

        Route::get('/workorders/{workOrder}', [\App\Http\Controllers\Technician\TechnicianController::class, 'show'])
            ->name('workorders.show');

        Route::post('/workorders/{workOrder}/accept', [TechnicianController::class, 'accept'])
            ->name('workorders.accept');

        Route::post('/workorders/{workOrder}/decline', [TechnicianController::class, 'decline'])
            ->name('workorders.decline');

        Route::post('/workorders/{workOrder}/field-status', [TechnicianController::class, 'updateFieldStatus'])
            ->name('workorders.field-status');


        Route::post('/workorders/{workOrder}/status', [TechnicianController::class, 'updateStatus'])
            ->name('workorders.status');
    });
// Offline page
Route::view('/offline', 'offline')->name('offline');


// routes for authentication
require __DIR__.'/auth.php';
