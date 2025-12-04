<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use DB;

class MessageTemplatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('message_templates')->insert([
            [
                'name' => 'incident_created',
                'channel' => 'email',
                'locale' => 'en',
                'subject' => 'Incident #{{id}} created',
                'body' => 'We registered your report {{id}}.',
                'is_active' => 1
            ],
            [
                'name' => 'incident_updated',
                'channel' => 'email',
                'locale' => 'en',
                'subject' => 'Incident #{{id}} update',
                'body' => 'Status is now {{status}}.',
                'is_active' => 1
            ],
        ]);
    }
}
