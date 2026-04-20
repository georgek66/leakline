<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;

class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::all();
        return view('admin.dashboard', compact('categories'));
    }
    public function create(Request $request){

        return view('admin.dashboard');
    }
    public function store(Request $request){
            $request->validate([
                'name' => 'required|unique:categories,name',
            ]);

            Category::create([
                'name' => $request->name
            ]);

            return redirect()->route('admin.dashboard')
                ->with('success', 'Category created successfully!');


    }
}
