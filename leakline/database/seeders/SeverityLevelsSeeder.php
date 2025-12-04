<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use DB;
class SeverityLevelsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('severity_levels')->insert([
            ['name' => 'low'],
            ['name' => 'medium'],
            ['name' => 'high'],
            ['name' => 'critical'],
        ]);
    }
}
