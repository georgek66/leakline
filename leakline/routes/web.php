<?php

use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Citizen\IncidentReportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('citizen.home');
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
Route::get('/citizen/report', [IncidentReportController::class, 'create'])
        ->name('citizen.report.create');

Route::post('/citizen/report', [IncidentReportController::class, 'store'])
    ->name('citizen.report.store');
Route::get('/citizen/track', function () {
    return 'Track page coming soon';
})->name('citizen.track.form');

require __DIR__.'/auth.php';
