<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $role = auth()->user()?->role?->name;

        return match ($role) {
            'admin'       => redirect()->route('admin.dashboard'),
            'technician'  => redirect()->route('technician.dashboard'),
            'coordinator' => redirect()->route('coordinator.dashboard'),
            default       => view('citizen.homepage'),
        };
    }

}
