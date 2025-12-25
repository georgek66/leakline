<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $role = auth()->user()?->role?->name;

        return match ($role) {
            'admin'       => view('admin.dashboard'),
            'technician'  => view('technician.dashboard'),
            'coordinator' => view('coordinator.dashboard'),
            default       => view('citizen.home'),
        };
    }

}
