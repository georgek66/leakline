<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use DB;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('roles')->insert([
            ['name' => 'admin', 'description' => 'Platform administrator'],
            ['name' => 'technician', 'description' => 'Field technician'],
            ['name' => 'coordinator', 'description' => 'Operations coordinator'],
            ['name' => 'viewer', 'description' => 'Read-only user'],
        ]);
    }
}
