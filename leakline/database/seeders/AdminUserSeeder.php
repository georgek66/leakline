<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use DB;
use Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            'role_id' => 1, // admin role
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('jesus'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
