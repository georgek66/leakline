<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use DB;
class SlaRulesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sev = DB::table('severity_levels')->pluck('id', 'name');

        DB::table('sla_rules')->insert([
            ['severity_id' => $sev['low'], 'response_time' => 24, 'resolution_time' => 168],
            ['severity_id' => $sev['medium'], 'response_time' => 8, 'resolution_time' => 72],
            ['severity_id' => $sev['high'], 'response_time' => 2, 'resolution_time' => 24],
            ['severity_id' => $sev['critical'], 'response_time' => 1, 'resolution_time' => 8],
        ]);
    }
}
