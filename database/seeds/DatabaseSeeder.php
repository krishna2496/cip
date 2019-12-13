<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call('TimezoneTableSeeder');
        $this->call('NotificationTypeTableSeeder');
        $this->call('TimesheetStatusTableSeeder');
        $this->call('AvailabilityTableSeeder');
    }
}
