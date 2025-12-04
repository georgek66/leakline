<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use DB;
class ResolutionCodesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('resolution_codes')->insert([
            [
                'code' => 'REP_PIPE',
                'label' => 'Repaired pipe',
                'default_estimated_savings_liters' => 5000
            ],
            [
                'code' => 'VALVE_ADJ',
                'label' => 'Valve adjusted',
                'default_estimated_savings_liters' => 1500
            ],
            [
                'code' => 'NO_FAULT',
                'label' => 'No fault found',
                'default_estimated_savings_liters' => 0
            ],
        ]);
    }
}
